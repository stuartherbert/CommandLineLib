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
 * @copyright   2011-present Stuart Herbert. www.stuartherbert.com
 * @copyright   2010 Gradwell dot com Ltd. www.gradwell.com
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://www.phix-project.org/
 * @version     @@PACKAGE_VERSION@@
 */

namespace Phix_Project\CommandLineLib4;

use Phix_Project\ContractLib2\Contract;
use Phix_Project\ValidationLib4\Validator;
use Phix_Project\ValidationLib4\ValidationList;

/**
 * Represents the definition of a single argument for a single switch
 */
class DefinedArg
{
        /**
         * The argument's name
         *
         * @var string
         */
        public $name;

        /**
         * The argument's description
         *
         * @var string
         */
        public $desc;

        /**
         * The default value of this argument, used if:
         *
         * 1. the switch isn't found on the command-line, or
         * 2. the switch is found with no argument and there is no
         *    implicit value set
         *
         * @var string
         */
        public $defaultValue = null;

        /**
         * The value used if the switch is activated without an argument
         *
         * @var  string
         */
        public $implicitValue = null;

        /**
         * Is this argument mandatory?
         *
         * @var boolean
         */
        public $isRequired = false;

        /**
         * How do we validate this argument before the calling app is
         * allowed to see it?
         *
         * @var ValidationList
         */
        protected $validators;

        /**
         * Define an argument to a switch
         *
         * @param string $argName
         * @param string $desc
         */
        public function __construct($argName, $desc)
        {
                $this->name = $argName;
                $this->desc = $desc;

                $this->validators = new ValidationList;
        }

        /**
         * Define this argument as being optional
         *
         * @return DefinedArg $this
         */
        public function setIsOptional()
        {
                $this->isRequired = false;
                return $this;
        }

        /**
         * Define this argument as being required
         *
         * @return DefinedArg $this
         */
        public function setIsRequired()
        {
                $this->isRequired = true;
                return $this;
        }

        public function setValidator(Validator $validator)
        {
                $this->validators->addValidator($validator);
                return $this;
        }

        /**
         * Is this argument optional?
         *
         * @return boolean
         */
        public function testIsOptional()
        {
                if ($this->isRequired == false)
                {
                        return true;
                }
                return false;
        }

        /**
         * Is this argument required?
         *
         * @return boolean
         */
        public function testIsRequired()
        {
                return $this->isRequired;
        }

        /**
         * Does this arg have a specific validator defined?
         *
         * @param string $validatorName
         * @return boolean
         */
        public function testMustValidateWith($validatorName)
        {
                // catch programming errors
                Contract::Preconditions(function() use($validatorName)
                {
                        Contract::RequiresValue($validatorName, is_string($validatorName), '$validatorName must be a string');
                        Contract::RequiresValue($validatorName, strlen($validatorName) > 0, '$validatorName cannot be an empty string');
                });

                foreach ($this->validators->getValidators() as $validator)
                {
                        if (get_class($validator) == $validatorName)
                        {
                                return true;
                        }
                }

                return false;
        }

        /**
         * Test a value to see if it is a permitted value for this argument
         *
         * We return an array of error messages. If the value is valid,
         * the returned array will be empty.
         *
         * @param mixed $value
         * @return ValidationResult
         */
        public function testIsValid($value)
        {
                return $this->validators->validate($value);
        }

        /**
         * Remember the default value for this arg
         *
         * @param mixed $value
         * @return DefinedArg $this
         */
        public function setDefaultValue($value)
        {
                $this->defaultValue = $value;
                return $this;
        }

        /**
         * Remember the implicit value for this arg
         *
         * @param mixed $value
         * @return DefinedArg $this
         */
        public function setImplicitValue($value)
        {
                $this->implicitValue = $value;
                return $this;
        }
}
