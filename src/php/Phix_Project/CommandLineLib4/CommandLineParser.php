<?php

/**
 * Copyright (c) 2011-present Stuart Herbert.
 * Copyright (c) 2010 Gradwell dot com Ltd.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package     Phix_Project
 * @subpackage  CommandLineLib4
 * @author      Stuart Herbert <stuart@stuartherbert.com>
 * @copyright   2011-present Stuart Herbert www.stuartherbert.com
 * @copyright   2010 Gradwell dot com Ltd. www.gradwell.com
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://www.phix-project.org/
 * @version     @@PACKAGE_VERSION@@
 */

namespace Phix_Project\CommandLineLib4;

use Phix_Project\ContractLib2\Contract;

/**
 * Main entry point for parsing a command line, looking for any expected
 * switches and their (possibly optional) arguments
 */
class CommandLineParser
{
        /**
         * Parse an array of command-line arguments, looking for command-
         * line switches
         *
         * @param array $args
         *      The array of command-line arguments (normally $argv)
         * @param int $argsIndex
         *      The current index inside $args to search from
         * @param DefinedSwitches $expectedOptions
         *      The list of command-line switches that we support
         * @return array(ParsedSwitches, int)
         *      The set of parsed command line switches, plus the new value
         *      for $argsIndex
         */
        public function parseCommandLine($args, $argsIndex, DefinedSwitches $expectedOptions)
        {
                // catch programming errors
                Contract::Preconditions(function() use ($args, $argsIndex, $expectedOptions)
                {
                        Contract::RequiresValue($args, is_array($args), '$args must be array');

                        Contract::RequiresValue($argsIndex, is_integer($argsIndex), '$argsIndex must be an integer');
                        Contract::RequiresValue($argsIndex, count($args) >= $argsIndex, '$argsIndex cannot be more than +1 beyond the end of $args');
                });

                // create our return value
                $return = new ParsedCommandLine();

                // create the container for switches that we will parse
                $parsedSwitches = new ParsedSwitches($expectedOptions);

                // find out how many args there are in total
                $argCount = count($args);

                // var_dump($args);

                // let's work through the args from left to right
                $done = false;
                while ($argsIndex < $argCount && !$done)
                {
                        // are we looking at a switch or not?
                        if ($args[$argsIndex] == '--')
                        {
                                // var_dump('Special case: --');
                                // special case - end of switches
                                // skip over it
                                $argsIndex++;
                                $done = true;
                        }
                        else if ($args[$argsIndex]{0} !== '-')
                        {
                                // var_dump('Not a switch');
                                // special case - end of switches
                                $done = true;
                        }
                        // yes we are ... parse it
                        // is it a short switch or a long switch?
                        else if ($args[$argsIndex]{1} !== '-')
                        {
                                // var_dump('Parsing short switch');
                                // var_dump('$argsIndex is: ' . $argsIndex);
                                // it is a short switch
                                $argsIndex = $this->parseShortSwitch($args, $argsIndex, $parsedSwitches, $expectedOptions);
                                // var_dump('$argsIndex is now: ' . $argsIndex);
                        }
                        else
                        {
                                // var_dump('Parsing long switch');
                                // it is a long switch
                                $argsIndex = $this->parseLongSwitch($args, $argsIndex, $parsedSwitches, $expectedOptions);
                        }
                }

                // now, we need to merge in the default values for any
                // arguments that have not been specified by the user
                $defaultValues = $expectedOptions->getDefaultValues();

                foreach ($defaultValues as $name => $value)
                {
                        if ($value !== null && $expectedOptions->getSwitchByName($name)->testHasArgument())
                        {
                                $parsedSwitches->addSwitchWithDefaultValueIfUnseen($expectedOptions, $name, $value);
                        }
                }

                // all done on the parsing side
                $return->switches  = $parsedSwitches->getSwitchesByOrder();
                $return->args      = array_slice($args, $argsIndex, count($argsIndex));

                // we need to validate the parsed switches
                //
                // we cannot do anything about the errors ... it has to be up
                // to the caller to deal with them!
                $return->errors = $parsedSwitches->validateSwitchValues();

                // all done
                return $return;
        }

        /**
         * Take a short switch, and see if it is one that we understand
         *
         * This parser supports short switches of the form:
         *
         * * -x
         *      where 'x' is a single switch
         * * -xfred
         *      where 'x' is a single switch, and 'fred' is its argument
         * * -x fred
         *      where 'x' is a single switch, and 'fred' is its argument
         * * -xyz
         *      where 'x', 'y' and 'z' are all short switches
         * * -xyz fred
         *      where 'x', 'y' and 'z' are all short switches, and 'fred'
         *      is the argument to switch 'z'
         *
         * These are all of the common short switch types traditionally
         * supported by UNIX-like systems
         *
         * All successfully parsed short switches are added to the
         * $parsedSwitches object.
         *
         * Any unexpected switches, or if there are any switches which are
         * missing their required argument, will trigger an exception
         *
         * @param array $args
         *      The array of command-line arguments (normally $argv)
         * @param int $argsIndex
         *      The current index inside $args to search from
         * @param ParsedSwitches $parsedSwitches
         *      The list of switches we have already parsed
         * @param DefinedSwitches $expectedOptions
         *      The list of command-line switches that we support
         * @return int
         *      The new value for $argsIndex
         */
        protected function parseShortSwitch($args, $argsIndex, ParsedSwitches $parsedSwitches, DefinedSwitches $expectedOptions)
        {
                // catch programming errors
                Contract::Preconditions(function() use ($args, $argsIndex, $expectedOptions)
                {
                        Contract::RequiresValue($args, is_array($args), '$args must be array');
                        Contract::RequiresValue($args, count($args) > 0, '$args cannot be an empty array');

                        Contract::RequiresValue($argsIndex, is_integer($argsIndex), '$argsIndex must be an integer');
                        Contract::RequiresValue($argsIndex, count($args) > $argsIndex, '$argsIndex cannot be beyond the end of $args');

                        Contract::RequiresValue($expectedOptions, count($expectedOptions->getSwitches()) > 0, '$expectedOptions must have some switches defined');
                });

                // $args[$argsIndex] contains one or more short switches,
                // which we expect to be defined in $expectedOptions

                $switchStringLength = strlen($args[$argsIndex]);
                for ($j = 1; $j < $switchStringLength; $j++)
                {
                        $shortSwitch = $args[$argsIndex]{$j};

                        // is this a valid switch?
                        if (!$expectedOptions->testHasShortSwitch($shortSwitch))
                        {
                                // we didn't expect this switch
                                throw new \Exception("unknown switch " . $shortSwitch);
                        }

                        // yes it is
                        $switch = $expectedOptions->getShortSwitch($shortSwitch);
                        $arg    = true;

                        // should it have an argument?
                        if ($switch->testHasArgument())
                        {
                                // yes, but it may be optional
                                // are we the first switch in this string?
                                if ($j == 1)
                                {
                                        // assume the rest of the string is the argument
                                        if ($j != $switchStringLength - 1)
                                        {
                                                list($arg, $argsIndex) = $this->parseArgument($args, $argsIndex, 2, $switch, '-' . $shortSwitch);
                                                // we've finished with this string,
                                                // so set $j to exit the loop
                                                $j = $switchStringLength - 1;
                                        }
                                        else
                                        {
                                                list($arg, $argsIndex) = $this->parseArgument($args, $argsIndex + 1, 0, $switch, '-' . $shortSwitch);
                                        }
                                }
                                else
                                {
                                        // are we at the end of the list of switches?
                                        if ($j != $switchStringLength - 1)
                                        {
                                                // no, we are not
                                                // this is invalid behaviour
                                                throw new \Exception('switch -' . $shortSwitch . ' expected argument');
                                        }

                                        list($arg, $argsIndex) = $this->parseArgument($args, $argsIndex + 1, 0, $switch, '-' . $shortSwitch);
                                }
                        }

                        // var_dump("Adding switch " . $switch->name);
                        $parsedSwitches->addSwitch($expectedOptions, $switch->name, $arg);
                }

                // increment our counter through the args
                $argsIndex++;

                // return the counter
                return $argsIndex;
        }

        /**
         * Take a long switch, and see if it is one that we are expecting
         *
         * This parser supports the following long switch formats:
         *
         * * --switch
         * * --switch=<argument>
         * * --switch <argument>
         *
         * These are all of the common long switch formats traditionally
         * supported on UNIX-like systems
         *
         * All successfully parsed long switches are added to the
         * $parsedSwitches object
         *
         * Any unexpected long switches, or any long switches that are
         * missing their argument, will trigger an exception
         *
         * @param array $args
         *      The array of command-line arguments (normally $argv)
         * @param int $argsIndex
         *      The current index inside $args to search from
         * @param ParsedSwitches $parsedSwitches
         *      The list of switches that we have already parsed
         * @param DefinedSwitches $expectedOptions
         *      The list of command-line switches that we support
         * @return int
         *      The new value of $argsIndex
         */
        protected function parseLongSwitch($args, $argsIndex, ParsedSwitches $parsedSwitches, DefinedSwitches $expectedOptions)
        {
                // catch programming errors
                Contract::Preconditions(function() use ($args, $argsIndex, $expectedOptions)
                {
                        Contract::RequiresValue($args, is_array($args), '$args must be array');
                        Contract::RequiresValue($args, count($args) > 0, '$args cannot be an empty array');

                        Contract::RequiresValue($argsIndex, is_integer($argsIndex), '$argsIndex must be an integer');
                        Contract::RequiresValue($argsIndex, count($args) > $argsIndex, '$argsIndex cannot be beyond the end of $args');

                        Contract::RequiresValue($expectedOptions, count($expectedOptions->getSwitches()) > 0, '$expectedOptions must have some switches defined');
                });

                // $args[i] contains a long switch, and might contain
                // a parameter too
                $equalsPos = strpos($args[$argsIndex], '=');
                if ($equalsPos !== false)
                {
                        $longSwitch = substr($args[$argsIndex], 2, $equalsPos - 2);
                }
                else
                {
                        $longSwitch = substr($args[$argsIndex], 2);
                }
                $arg = null;

                // is this a switch we expected?
                if (!$expectedOptions->testHasLongSwitch($longSwitch))
                {
                        throw new \Exception("unknown switch " . $longSwitch);
                }

                // yes it is
                $switch = $expectedOptions->getLongSwitch($longSwitch);

                // should it have an argument?
                if ($switch->testHasArgument())
                {
                        // did we find one earlier?
                        if ($equalsPos !== false)
                        {
                                // yes we did
                                list($arg, $argsIndex) = $this->parseArgument($args, $argsIndex, $equalsPos + 1, $switch, '--' . $longSwitch);
                        }
                        else
                        {
                                // no we did not; it might be next
                                list($arg, $argsIndex) = $this->parseArgument($args, $argsIndex + 1, 0, $switch, '--' . $longSwitch);
                        }
                }

                // increment to the next item in the list
                $argsIndex++;

                $parsedSwitches->addSwitch($expectedOptions, $switch->name, $arg);

                // all done
                return $argsIndex;
        }

        /**
         * Examine the command line for a (possibly optional) argument
         * for a switch that we have just found on that command line
         *
         * @param array $args
         *      The array of command-line arguments (normally $argv)
         * @param int $argsIndex
         *      The current index inside $args to search from.
         *      This may be just beyond the end of the command-line args
         *      if the last command-line argument is a switch.
         * @param int $startFrom
         *      The offset inside $args[$argsIndex] where the argument string
         *      starts
         * @param DefinedSwitch $switch
         *      The command-line switch that may need an argument
         * @param string $switchSeen
         *      The actual switch we found on the command-line
         * @return array(string, int)
         *      The argument we have parsed, plus the new value of $argsIndex
         */
        protected function parseArgument($args, $argsIndex, $startFrom, DefinedSwitch $switch, $switchSeen)
        {
                // catch programming errors
                Contract::Preconditions(function() use ($args, $argsIndex, $startFrom, $switchSeen)
                {
                        Contract::RequiresValue($args, is_array($args), '$args must be array');
                        Contract::RequiresValue($args, count($args) > 0, '$args cannot be an empty array');

                        Contract::RequiresValue($argsIndex, is_integer($argsIndex), '$argsIndex must be an integer');
                        Contract::RequiresValue($argsIndex, count($args) >= $argsIndex, '$argsIndex cannot be more than +1 beyond the end of $args');

                        // this is a conditional test because it is legal
                        // for $args[$argsIndex] to be unset()
                        if (isset($args[$argsIndex]))
                        {
                                Contract::RequiresValue($startFrom, $startFrom <= strlen($args[$argsIndex]), '$startFrom cannot be more than +1 beyond the end of $args[$argsIndex]');
                        }

                        Contract::RequiresValue($switchSeen, is_string($switchSeen), '$switchSeen must be a string');
                        Contract::RequiresValue($switchSeen, strlen($switchSeen) > 0, '$switchSeen cannot be an empty string');
                });

                // initialise the return value
                $arg = null;

                // is the argument optional or required?
                if ($switch->testHasOptionalArgument())
                {
                        // it is optional ... but is
                        // it there?
                        if (isset($args[$argsIndex]))
                        {
                                // yes it is
                                $arg = substr($args[$argsIndex], $startFrom);
                        }
                        else
                        {
                                $arg = $switch->arg->defaultValue;
                                $argsIndex--;
                        }
                }
                else
                {
                        // argument is required ... but
                        // is it there?
                        if (!isset($args[$argsIndex]))
                        {
                                // no it is not
                                // error!
                                throw new \Exception('switch ' . $switchSeen . ' expected argument');
                        }

                        // yes it is
                        $arg = substr($args[$argsIndex], $startFrom);

                        // did we get an argument?
                        if (strlen(trim($arg)) == 0)
                        {
                                // no, we did not
                                throw new \Exception('switch ' . $switchSeen . ' expected argument');
                        }
                }

                return array($arg, $argsIndex);
        }
}
