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
 * @copyright   2011 Stuart Herbert. www.stuartherbert.com
 * @copyright   2010 Gradwell dot com Ltd. www.gradwell.com
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://www.phix-project.org
 * @version     @@PACKAGE_VERSION@@
 */

namespace Phix_Project\CommandLineLib4;

use Phix_Project\ContractLib2\Contract;

/**
 * Represents a switch that has been parsed from the command-line
 */
class ParsedSwitch
{
        /**
         * The full definition for this switch
         *
         * @var Phix_Project\CommandLineLib4\DefinedSwitch
         */
        public $definition = null;

        /**
         * The name of this switch
         *
         * @var string
         */
        public $name = null;

        /**
         * Any arguments that have been passed to this switch
         *
         * @var array
         */
        public $values  = array();

        /**
         * How many times this switch has been seen on the command-line
         *
         * @var int
         */
        public $invokes = 0;

        /**
         * For switches with optional arguments, are we using the argument's
         * default value (because the argument wasn't supplied on the
         * command line)?
         *
         * @var boolean
         */
        public $isUsingDefaultValue = false;

        /**
         * Constructor
         *
         * @param DefinedSwitch $switch
         */
        public function __construct(DefinedSwitch $switch)
        {
                $this->definition = $switch;
                $this->name       = $switch->name;
        }

        /**
         * Increment the counter of how many times the parser has seen
         * this switch on the command-line
         */
        public function addToInvokeCount()
        {
                $this->invokes++;
        }

        /**
         * Add an argument's value to the list that the parser has seen
         * for this switch on the command-line
         *
         * @param string $value
         */
        public function addValue($value)
        {
                $this->values[] = $value;
        }

        /**
         * Remember that the parser has not seen this switch's argument,
         * and so we are having to use the argument's defined default
         * value
         */
        public function setIsUsingDefaultValue()
        {
                $this->isUsingDefaultValue = true;
        }

        /**
         * Run the switch's argument's validators against the list of
         * values that the command-line parser found earlier
         *
         * @return array
         *      a list of error messages
         *      this list is empty if the validators all pass
         */
        public function validateValues()
        {
                $return = array();

                // do we have any arguments at all?
                if (!$this->definition->testHasArgument())
                {
                        // no, so impossible to fail validation
                        return $return;
                }

                // to improve readability, we need to prefix all of our
                // error messages with the switch
                $errorPrefix = $this->definition->getHumanReadableSwitch();

                // are all the arguments valid?
                foreach ($this->values as $value)
                {
                        $result = $this->definition->arg->testIsValid($value);
                        if ($result->hasErrors())
                        {
                                // prefix our switch to the front of the errors,
                                // so that the end-user knows which switch got
                                // him/her into trouble
                                foreach ($result->getErrors() as $errorMsg)
                                {
                                        $return[] = $errorPrefix . ': ' . $errorMsg;
                                }
                        }
                }

                // all done
                return $return;
        }

        /**
         * Get the first value seen for this switch
         *
         * This is a helper method for those switches that cannot be
         * repeated on the command-line (and therefore, cannot have more
         * than one parsed value)
         *
         * @return string
         */
        public function getFirstValue()
        {
                if (count($this->values) > 0)
                {
                        return $this->values[0];
                }

                return null;
        }

        /**
         * Is this switch's argument using the default value, from the
         * switch's argument's definition?
         *
         * @return boolean
         */
        public function testIsDefaultValue()
        {
                return $this->isUsingDefaultValue;
        }
}
