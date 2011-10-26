<?php

/**
 * Copyright (c) 2011 Stuart Herbert.
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
 * @subpackage  CommandLineLib
 * @author      Stuart Herbert <stuart@stuartherbert.com>
 * @copyright   2011 Stuart Herbert. www.stuartherbert.com
 * @copyright   2010 Gradwell dot com Ltd. www.gradwell.com
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://www.phix-project.org
 * @version     @@PACKAGE_VERSION@@
 */

namespace Phix_Project\CommandLineLib;

use Phix_Project\ValidationLib\Validator;

/**
 * Represents a single definition of a single command-line switch
 * 
 * We only need one DefinedSwitch to represent all of the valid forms of
 * a single switch
 */
class DefinedSwitch
{
        /**
         * The name of the switch
         * 
         * @var string
         */
        public $name;
        
        /**
         * The switch's short description
         * 
         * This can be used by the calling app, when outputing help to
         * the user
         * 
         * @var string
         */
        public $desc;
        
        /**
         * The switch's long description
         * 
         * This can be used by the calling app, when outputing help to
         * the user
         * 
         * @var string
         */
        public $longdesc;
        
        /**
         * A list of the different short switches
         * 
         * Any one switch can have multiple short switches. The classic
         * example is '-?' and '-h', both of which do the same thing in
         * well-behaved command-line applications
         * 
         * @var array(string)
         */
        public $shortSwitches = array();
        
        /**
         * A list of the different long switches
         * 
         * Any one switch can have multiple long switches. The classic
         * example is '--?' and '--help', both of which do the same thing
         * in well-behaved command-line applications
         * 
         * @var array(string)
         */
        public $longSwitches = array();

        /**
         * The argument (if any) that this switch expects
         * 
         * @var DefinedArg
         */
        public $arg = null;
        
        /**
         * A bitset of flags that affect how this switch is parsed by
         * the command-line parser
         * 
         * @var int
         */
        public $flags = null;

        /**
         * The default behaviour flag
         */
        const FLAG_NONE = 0;
        
        /**
         * The behaviour flag for switches that can be repeated on the
         * command-line
         * 
         * The classic example of such a switch is '-vvv', where additional
         * repeats make the app more and more verbose
         */
        const FLAG_REPEATABLE = 1;

        /**
         * Constructor
         * 
         * @param string $name
         *      The switch's name, used as it's ID everywhere in the
         *      command-line parser
         * @param string $desc 
         *      The switch's short description
         */
        public function __construct($name, $desc)
        {
                $this->name = $name;
                $this->desc = $desc;
        }

        /**
         * Set it so that this switch is allowed to be repeated on
         * the command-line by the user
         * 
         * @return DefinedSwitch
         */
        public function setSwitchIsRepeatable()
        {
                $this->flags |= self::FLAG_REPEATABLE;
                return $this;
        }

        /**
         * Add a short switch (a single letter or number) to the list
         * of permitted options
         *
         * @param string $switch
         * @return DefinedSwitch
         */
        public function setWithShortSwitch($switch)
        {
                // make sure the switch does not start with a '-'!!
                if ($switch{0} == '-')
                {
                        throw new \Exception("do not start a switch with the '-' character");
                }

                // if we get here, the switch is fine
                $this->shortSwitches[$switch] = $switch;
                return $this;
        }

        /**
         * Add a long switch (usually a word) to the list of permitted
         * options
         *
         * @param string $switch
         * @return DefinedSwitch
         */
        public function setWithLongSwitch($switch)
        {
                // make sure the switch does not start with a '-'!!
                if ($switch{0} == '-')
                {
                        throw new \Exception("do not start a switch with the '-' character");
                }

                // if we get here, the switch is fine
                $this->longSwitches[$switch] = $switch;
                return $this;
        }

        /**
         * Add an optional argument that this switch accepts
         *
         * @param string $argName the name of the argument
         * @param string $argDesc the argument's description
         * @return DefinedSwitch
         */
        public function setWithOptionalArg($argName, $argDesc)
        {
                $this->arg = new DefinedArg($argName, $argDesc);
                $this->arg->setIsOptional();
                return $this;
        }

        /**
         * Add an argument that this switch requires
         * 
         * @param string $argName the name of the argument
         * @param string $argDesc the argument's description
         * @return DefinedSwitch
         */
        public function setWithRequiredArg($argName, $argDesc)
        {
                $this->arg = new DefinedArg($argName, $argDesc);
                $this->arg->setIsRequired();
                return $this;
        }

        /**
         * Set the default value for the argument that this switch
         * expects
         * 
         * This only makes sense if the argument is optional
         * 
         * @param string $value the default value for the argument
         * @return DefinedSwitch 
         */
        public function setArgHasDefaultValueOf($value)
        {
                $this->requireValidArg();
                $this->arg->setDefaultValue($value);
                return $this;
        }

        /**
         * Add Validator object to the switch's argument
         * 
         * The Validators are run, in the order that they've been added,
         * when the command-line parser finds the argument for this switch.
         * They are used to cover the very basics, but sophisticated
         * Validators could be created and added too.
         * 
         * @param Validator $validator
         * @return DefinedSwitch 
         */
        public function setArgValidator(Validator $validator)
        {
                $this->requireValidArg();
                $this->arg->setValidator($validator);
                return $this;
        }

        /**
         * Provide a longer description of this switch, to be shown during
         * the output of extended help information
         * 
         * @param string $desc
         * @return DefinedSwitch $this
         */
        public function setLongDesc($desc)
        {
                $this->longdesc = $desc;
                return $this;
        }
        /**
         * Obtain a list of the short switches that have been defined
         *
         * @return array
         */
        public function getShortSwitches()
        {
                return $this->shortSwitches;
        }

        /**
         * Obtain a list of the long switches that have been defined
         *
         * @return array
         */
        public function getLongSwitches()
        {
                return $this->longSwitches;
        }

        /**
         * Has $shortSwitch been defined?
         *
         * @param string $shortSwitch
         * @return boolean
         */
        public function testHasShortSwitch($shortSwitch)
        {
                if (isset($this->shortSwitches[$shortSwitch]))
                {
                        return true;
                }
                return false;
        }

        /**
         * Has $longSwitch been defined?
         *
         * @param string $longSwitch
         * @return boolean
         */
        public function testHasLongSwitch($longSwitch)
        {
                if (isset($this->longSwitches[$longSwitch]))
                {
                        return true;
                }
                return false;
        }

        /**
         * Does this switch accept a (possibly optional) argument?
         *
         * @return boolean
         */
        public function testHasArgument()
        {
                if (! $this->arg instanceof DefinedArg)
                {
                        return false;
                }

                return true;
        }

        /**
         * Does this switch accept an optional argument?
         *
         * @return boolean
         */
        public function testHasOptionalArgument()
        {
                if (! $this->arg instanceof DefinedArg)
                {
                        return false;
                }

                return $this->arg->testIsOptional();
        }

        /**
         * Does this switch require an argument?
         *
         * @return boolean
         */
        public function testHasRequiredArgument()
        {
                if (! $this->arg instanceof DefinedArg)
                {
                        return false;
                }

                 return $this->arg->testIsRequired();
        }

        /**
         * Is the user allowed to use this switch more than once?
         *
         * @return boolean
         */
        public function testIsRepeatable()
        {
                if($this->flags & self::FLAG_REPEATABLE)
                {
                        return true;
                }
                return false;
        }

        /**
         * Return a list of the different forms of this switch, in a form
         * that is suitable for building up human-readable help messages
         * 
         * @return array
         */
        public function getHumanReadableSwitchList()
        {
                $return = array();
                $shortSwitches = array();
                $longSwitches = array();

                foreach ($this->shortSwitches as $shortSwitch)
                {
                        $switch = '-' . $shortSwitch;
                        $shortSwitches[$switch] = $switch;
                }
                foreach ($this->longSwitches as $longSwitch)
                {
                        $switch = '--' . $longSwitch;
                        $longSwitches[$switch] = $switch;
                }

                \ksort($shortSwitches);
                \ksort($longSwitches);

                return array_merge($shortSwitches, $longSwitches);
        }

        /**
         * Make sure we have an argument defined
         */
        protected function requireValidArg()
        {
                if (! $this->arg instanceof DefinedArg)
                {
                        throw new \Exception("You must set a require or an optional argument before you can set options on it");
                }
        }
}
