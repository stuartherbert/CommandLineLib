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

/**
 * Container for all of the DefinedSwitches that we expect to find in the
 * same place when parsing a command-line
 */
class DefinedSwitches
{
        /**
         * A dynamic cache of all of the short switches that have been
         * defined
         * 
         * @var array(DefinedSwitch)
         */
        public $shortSwitches = array();
        
        /**
         * A dynamic cache of all of the long switches that have been
         * defined
         * 
         * @var array(DefinedSwitch)
         */
        public $longSwitches = array();
        
        /**
         * A list of all of the switches that have been defined
         * 
         * @var array(DefinedSwitch)
         */
        public $switches = array();

        /**
         *
         * @var boolean
         */
        protected $allSwitchesLoaded = false;

        /**
         * Add a switch to the list of allowed switches
         * 
         * @param string $name
         * @param string $desc
         * @return DefinedSwitch 
         */
        public function addSwitch($name, $desc)
        {
                // note that our cache of introspected switches
                // is now invalid
                $this->allSwitchesLoaded = false;

                // create and add the switch
                $this->switches[$name] = new DefinedSwitch($name, $desc);

                // return the switch for further configuration
                // by the caller
                return $this->switches[$name];
        }

        /**
         * Do we have the definition of a specific switch?
         * 
         * @param string $switchName
         * @return boolean
         */
        public function testHasSwitchByName($switchName)
        {
                if (isset($this->switches[$switchName]))
                {
                        return true;
                }

                return false;
        }
        
        /**
         * Do we have a given short switch?
         * 
         * @param string $switch
         * @return boolean
         */
        public function testHasShortSwitch($switch)
        {
                // make sure the cache is complete
                $this->ensureSwitchCacheIsComplete();

                if (isset($this->shortSwitches[$switch]))
                {
                        return true;
                }

                return false;
        }

        /**
         * Get the definition of a given short switch
         * 
         * @param string $switchName
         * @return DefinedSwitch 
         */
        public function getShortSwitch($switchName)
        {
                // make sure the cache is complete
                $this->ensureSwitchCacheIsComplete();

                if (isset($this->shortSwitches[$switchName]))
                {
                        return $this->shortSwitches[$switchName];
                }

                throw new \Exception("unknown switch $switch");
        }

        /**
         * Do we have a definition for a given long switch?
         * 
         * @param string $switch
         * @return boolean
         */
        public function testHasLongSwitch($switch)
        {
                // make sure the cache is complete
                $this->ensureSwitchCacheIsComplete();

                if (isset($this->longSwitches[$switch]))
                {
                        return true;
                }

                return false;
        }

        /**
         * Get the definition for a long switch
         * 
         * @param string $switch
         * @return DefinedSwitch
         */
        public function getLongSwitch($switch)
        {
                // make sure the cache is complete
                $this->ensureSwitchCacheIsComplete();

                if (isset($this->longSwitches[$switch]))
                {
                        return $this->longSwitches[$switch];
                }

                throw new \Exception("unknown switch $switch");
        }

        /**
         * Get the definition of a switch, by its name
         * 
         * @param string $name
         * @return DefinedSwitch
         */
        public function getSwitchByName($name)
        {
                if (isset($this->switches[$name]))
                {
                        return $this->switches[$name];
                }

                throw new \Exception("unknown switch $switch");
        }

        /**
         * Get the complete list of defined switches
         * 
         * @return array(DefinedSwitch)
         */
        public function getSwitches()
        {
                return $this->switches;
        }

        /**
         * Get a complete list of default values for all switches that
         * take arguments
         * 
         * @return array(string)
         */
        public function getDefaultValues()
        {
                $return = array();

                foreach ($this->switches as $name => $switch)
                {
                        if ($switch->testHasArgument() && isset($switch->arg->defaultValue))
                        {
                                $return[$name] = $switch->arg->defaultValue;
                        }
                        else
                        {
                                $return[$name] = null;
                        }
                }

                return $return;
        }

        /**
         * Get an array of all of the switches in this definition, sorted
         * into the right order to display in a help message
         * 
         * This is for apps like phix, which display a help message that
         * is designed to look like a UNIX manual page
         * 
         * @return array(DefinedSwitch)
         */
        public function getSwitchesInDisplayOrder()
        {
                // turn the list into something that's suitably sorted
                $shortSwitchesWithoutArgs = array();
                $shortSwitchesWithArgs = array();
                $longSwitchesWithoutArgs = array();
                $longSwitchesWithArgs = array();

                $allShortSwitches = array();
                $allLongSwitches = array();

                $allSwitches = $this->switches;

                foreach ($allSwitches as $switch)
                {
                        foreach ($switch->shortSwitches as $shortSwitch)
                        {
                                $allShortSwitches['-' . $shortSwitch] = $switch;

                                if ($switch->testHasArgument())
                                {
                                        $shortSwitchesWithArgs[$shortSwitch] = $switch;
                                }
                                else
                                {
                                        $shortSwitchesWithoutArgs[$shortSwitch] = $shortSwitch;
                                }
                        }

                        foreach ($switch->longSwitches as $longSwitch)
                        {
                                $allLongSwitches['--' . $longSwitch] = $switch;

                                if ($switch->testHasArgument())
                                {
                                        $longSwitchesWithArgs[$longSwitch] = $switch;
                                }
                                else
                                {
                                        $longSwitchesWithoutArgs[$longSwitch] = $longSwitch;
                                }
                        }
                }

                // we have all the switches that phix supports
                // let's put them into sensible orders, and then display
                // them
                \ksort($shortSwitchesWithArgs);
                \ksort($shortSwitchesWithoutArgs);
                \ksort($longSwitchesWithArgs);
                \ksort($longSwitchesWithoutArgs);
                \ksort($allShortSwitches);
                \ksort($allLongSwitches);

                $return = array (
                        'shortSwitchesWithArgs' => $shortSwitchesWithArgs,
                        'shortSwitchesWithoutArgs' => $shortSwitchesWithoutArgs,
                        'longSwitchesWithArgs' => $longSwitchesWithArgs,
                        'longSwitchesWithoutArgs' => $longSwitchesWithoutArgs,
                        'allSwitches' => array_merge($allShortSwitches, $allLongSwitches),
                );

                return $return;
        }

        /**
         * Tell this object that we've finished adding switches, so that
         * it can work out what short and long switches have been defined
         */
        protected function ensureSwitchCacheIsComplete()
        {
                // is the cache up to date?
                if ($this->allSwitchesLoaded)
                {
                        // yes ... no action needed
                        return;
                }

                // if we get here, the cache needs rebuilding
                foreach ($this->switches as $switch)
                {
                        foreach ($switch->getShortSwitches() as $shortSwitch)
                        {
                                $this->shortSwitches[$shortSwitch] = $switch;
                        }

                        foreach ($switch->getLongSwitches() as $longSwitch)
                        {
                                $this->longSwitches[$longSwitch] = $switch;
                        }
                }

                // all done
                $this->allSwitchesLoaded = true;
        }
}
