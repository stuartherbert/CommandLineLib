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
 * @subpackage  CommandLineLib3
 * @author      Stuart Herbert <stuart@stuartherbert.com>
 * @copyright   2011-present Stuart Herbert. www.stuartherbert.com
 * @copyright   2010 Gradwell dot com Ltd. www.gradwell.com
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://www.phix-project.org
 * @version     @@PACKAGE_VERSION@@
 */

namespace Phix_Project\CommandLineLib4;

use Phix_Project\ContractLib2\Contract;

/**
 * Container for all of the switches parsed by the command-line parser
 */
class ParsedSwitches
{
        /**
         * A list of the switches that have been parsed, indexed by the
         * name of the switch's DefinedSwitch
         *
         * @var array(ParsedSwitch)
         */
        protected $switchesByName  = array();

        /**
         * A list of the switches that have been parsed, in the order
         * that the parser found them on the command line
         *
         * @var array
         */
	protected $switchesByOrder = array();

        /**
         * Add a switch to this collection
         *
         * @param DefinedSwitches $expectedOptions
         *      A list of the switches that are allowed
         * @param string $name
         *      The name of the switch to add to this collection
         * @param  $arg
         *      The value of any argument found by the parser
         */
        public function addSwitch(DefinedSwitches $expectedOptions, $name, $arg = true)
        {
                // catch programming errors
                Contract::PreConditions(function() use ($name)
                {
                        Contract::RequiresValue($name, is_string($name), '$name must be a string');
                        Contract::RequiresValue($name, strlen($name) > 0, '$name cannot be an empty string');
                });

                $this->requireValidExpectedSwitchName($expectedOptions, $name);
                $this->addSwitchByName($expectedOptions, $name, $arg);
                $this->addSwitchByOrder($expectedOptions, $name, $arg);
        }

        /**
         * Add a switch to our 'by-name' indexed list
         *
         * @param DefinedSwitches $expectedOptions
         *      A list of the switches that are allowed
         * @param string $name
         *      The name of the switch to add to this collection
         * @param string $arg
         *      The value of any argument found by the parser
         * @param boolean $isDefaultValue
         *      True if $arg is the default value for this switch
         */
        protected function addSwitchByName(DefinedSwitches $expectedOptions, $name, $arg, $isDefaultValue = false)
        {
                // catch programming errors
                Contract::PreConditions(function() use ($name, $isDefaultValue)
                {
                        Contract::RequiresValue($name, is_string($name), '$name must be a string');
                        Contract::RequiresValue($name, strlen($name) > 0, '$name cannot be an empty string');

                        Contract::RequiresValue($isDefaultValue, is_bool($isDefaultValue), '$isDefaultValue must be a boolean value');
                });

                if (!isset($this->switchesByName[$name]))
                {
                        $this->switchesByName[$name] = new ParsedSwitch($expectedOptions->getSwitchByName($name));
                }
                $this->switchesByName[$name]->addToInvokeCount();
                $this->switchesByName[$name]->addValue($arg);

                if ($isDefaultValue)
                {
                        $this->switchesByName[$name]->setIsUsingDefaultValue();
                }
        }

        /**
         * Add a switch to our 'by-order' indexed list
         *
         * @param DefinedSwitches $expectedOptions
         *      A list of the switches that are allowed
         * @param string $name
         *      The name of the switch to add to this collection
         * @param type $arg
         *      The value of any argument found by the parser
         * @param boolean $isDefaultValue
         *      True if $arg is the default value for this switch
         */
        protected function addSwitchByOrder(DefinedSwitches $expectedOptions, $name, $arg, $isDefaultValue = false)
        {
                // catch programming errors
                Contract::PreConditions(function() use ($name, $isDefaultValue)
                {
                        Contract::RequiresValue($name, is_string($name), '$name must be a string');
                        Contract::RequiresValue($name, strlen($name) > 0, '$name cannot be an empty string');

                        Contract::RequiresValue($isDefaultValue, is_bool($isDefaultValue), '$isDefaultValue must be a boolean value');
                });

                $parsedOption = new ParsedSwitch($expectedOptions->getSwitchByName($name));
                $parsedOption->addToInvokeCount();
                $parsedOption->addValue($arg);
                if ($isDefaultValue)
                {
                        $parsedOption->setIsUsingDefaultValue();
                }

		$this->switchesByOrder[] = $parsedOption;
        }

        /**
         * Add a switch that the user never supplied on the command line,
         * but which does have a default value.
         *
         * This is used by the CommandLineParser to merge in all switches
         * that have a default value, leaving it to us to make sure that
         * we do not overwrite any switches that the CommandLineParser
         * previously found
         *
         * @param DefinedSwitches $expectedOptions
         * @param type $switchName
         * @param type $value
         * @return type
         */

        public function addSwitchWithDefaultValueIfUnseen(DefinedSwitches $expectedOptions, $switchName, $value)
        {
                $this->requireValidExpectedSwitchName($expectedOptions, $switchName);

                // has this switch already been invoked by the user?
                if (isset($this->switchesByName[$switchName]))
                {
                        // yes
                        //
                        // we are only allowed to add switches that we
                        // have not seen before!
                        //
                        // NOTE: this is not a programming error when this
                        //       occurs
                        return;
                }

                $this->addSwitchByName($expectedOptions, $switchName, $value, true);
                $this->addSwitchByOrder($expectedOptions, $switchName, $value, true);
        }

        /**
         * Have we already seen this switch?
         *
         * @param string $switchName
         * @return boolean
         */
        public function testHasSwitch($switchName)
        {
                if (isset($this->switchesByName[$switchName]))
                {
                        return true;
                }

                return false;
        }

        /**
         * Get a full list of the switches that we have
         *
         * The returned list is indexed by name, not by the order that
         * we have seen the switches
         *
         * @return boolean
         */
        public function getSwitches()
        {
                return $this->switchesByName;
        }

        /**
         * Return a switch
         *
         * Throws an Exception if the switch is one we have not seen yet
         *
         * @param string $switchName
         * @return DefinedSwitch
         */
        public function getSwitch($switchName)
        {
                $this->requireValidSwitchName($switchName);
                return $this->switchesByName[$switchName];
        }

        /**
         * Get a list of the switches that we have seen
         *
         * The returned list is sorted in the order that we saw the switches
         *
         * @return array
         */
        public function getSwitchesByOrder()
        {
                return $this->switchesByOrder;
        }

        /**
         * Get all of the values we've seen for a given switch's argument
         *
         * Multiple values will exist only for switches that are allowed
         * to repeat on the command-line
         *
         * @param string $switchName
         * @return array
         */
        public function getArgsForSwitch($switchName)
        {
                $this->requireValidSwitchName($switchName);
                return $this->switchesByName[$switchName]->values;
        }

        /**
         * Get the value we've seen for a given switch's argument
         *
         * This is the very useful method to call when you know that the
         * switch you're interested in is never ever allowed to repeat
         * on the command-line
         *
         * @param string $switchName
         * @return string
         */
        public function getFirstArgForSwitch($switchName)
        {
                $this->requireValidSwitchName($switchName);
                return $this->switchesByName[$switchName]->values[0];
        }

        /**
         * How many times has a switch been seen on the command-line?
         *
         * @param string $switchName
         * @return int
         */
        public function getInvokeCountForSwitch($switchName)
        {
                if (!isset($this->switchesByName[$switchName]))
                {
                        return 0;
                }

                return $this->switchesByName[$switchName]->invokes;
        }

        /**
         * For each switch that we have seen, run any validators that have
         * been defined for that switch.
         *
         * @return array
         *      The error messages returned by the validators
         */
        public function validateSwitchValues()
        {
                $return = array();

                // loop over the switches
                foreach ($this->switchesByName as $name => $switch)
                {
                        $validationErrors = $switch->validateValues();
                        $return = array_merge($return, $validationErrors);
                }

                return $return;
        }

        /**
         * Make sure that we have seen a named switch before
         *
         * If we have not seen the switch, throw an Exception.  This is
         * a helper method to make sure that we fail fast, fail hard if
         * a programmatic error has occurred at runtime
         *
         * @param string $switchName
         */
        protected function requireValidSwitchName($switchName)
        {
                if (!$this->testHasSwitch($switchName))
                {
                        throw new \Exception("Unknown switch name " . $switchName);
                }
        }

        /**
         * Make sure that a named switch is in the list of defined switches
         * that we are allowed to accept
         *
         * If the switch isn't in the list of defined switches, throw an
         * Exception.  This is a helper method to make sure that we fail
         * fast, fail hard if a programmatic error has occurred at runtime.
         *
         * @param DefinedSwitches $expectedOptions
         * @param string $switchName
         */
        protected function requireValidExpectedSwitchName(DefinedSwitches $expectedOptions, $switchName)
        {
                if (!$expectedOptions->testHasSwitchByName($switchName))
                {
                        throw new \Exception("Unknown switch name " . $switchName);
                }
        }
}
