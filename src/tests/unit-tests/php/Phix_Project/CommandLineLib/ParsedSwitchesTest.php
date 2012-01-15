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

use Phix_Project\ValidationLib\MustBeString;
use Phix_Project\ValidationLib\MustBeInteger;

class ParsedSwitchesTest extends \PHPUnit_Framework_TestCase
{
        public function testCanAddSwitch()
        {
                // define the options we are expecting
                $expectedOptions = new DefinedSwitches();

                // create the switch to add
                $switchName = 'fred';
                $switchDesc = 'trout';
                $expectedOptions->addSwitch($switchName, $switchDesc);

                // create the ParsedSwitches object
                $parsedSwitches = new ParsedSwitches();
                $parsedSwitches->addSwitch($expectedOptions, $switchName);

                // did it work?
                $this->assertTrue($parsedSwitches->testHasSwitch($switchName));

                // and what happens if we try to add a switch
                // that has not been defined?
                $caughtException = false;
                try
                {
                        $parsedSwitches->addSwitch($expectedOptions, 'harry');
                }
                catch (\Exception $e)
                {
                        $caughtException = true;
                }
                $this->assertTrue($caughtException);
        }

        public function testCanCheckForSwitch()
        {
                // define the options we are expecting
                $expectedOptions = new DefinedSwitches();

                // create the switch to add
                $switchName = 'fred';
                $switchDesc = 'trout';
                $expectedOptions->addSwitch($switchName, $switchDesc);

                // create the ParsedSwitches object
                $parsedSwitches = new ParsedSwitches($expectedOptions);
                $parsedSwitches->addSwitch($expectedOptions, $switchName);

                // did it work?
                $this->assertTrue($parsedSwitches->testHasSwitch($switchName));

                // if the switch is not there?
                $this->assertFalse($parsedSwitches->testHasSwitch('harry'));
        }

        public function testCanGetSwitchByName()
        {
                // define the options we are expecting
                $expectedOptions = new DefinedSwitches();

                // create the switches to add
                $switchName1 = 'fred';
                $switchDesc1 = 'trout';
                $expectedOptions->addSwitch($switchName1, $switchDesc1);
                $switch1 = $expectedOptions->getSwitchByName($switchName1);

                $switchName2 = 'harry';
                $switchDesc2 = 'salmon';
                $expectedOptions->addSwitch($switchName2, $switchDesc2);
                $switch2 = $expectedOptions->getSwitchByName($switchName2);

                // create the ParsedSwitches object
                $parsedSwitches = new ParsedSwitches($expectedOptions);
                $parsedSwitches->addSwitch($expectedOptions, $switchName1);
                $parsedSwitches->addSwitch($expectedOptions, $switchName2);

                // did it work?
                $retrievedSwitch1 = $parsedSwitches->getSwitch($switchName1);
                $retrievedSwitch2 = $parsedSwitches->getSwitch($switchName2);

                $this->assertTrue($retrievedSwitch1 instanceof ParsedSwitch);
                $this->assertEquals($switch1->name, $retrievedSwitch1->name);
                $this->assertSame($switch1, $retrievedSwitch1->definition);

                $this->assertTrue($retrievedSwitch2 instanceof ParsedSwitch);
                $this->assertEquals($switch2->name, $retrievedSwitch2->name);
                $this->assertSame($switch2, $retrievedSwitch2->definition);

                // and if the switch is not there?
                $caughtException = false;
                try
                {
                        $parsedSwitches->getSwitch('notdefined');
                }
                catch (\Exception $e)
                {
                        $caughtException = true;
                }
                $this->assertTrue($caughtException);
        }

        public function testCanGetSwitchArgValues()
        {
                // define the options we are expecting
                $expectedOptions = new DefinedSwitches();

                $expectedOptions->addSwitch('fred', 'trout')
                                ->setWithOptionalArg('<fish>', 'which kind of fish you like');

                $parsedSwitches = new ParsedSwitches();
                $parsedSwitches->addSwitch($expectedOptions, 'fred', 'salmon');

                // can we do this?
                $retrievedArgs = $parsedSwitches->getArgsForSwitch('fred');

                // did it work?
                $this->assertTrue(is_array($retrievedArgs));
                $this->assertEquals(1, count($retrievedArgs));
                $this->assertEquals('salmon', $retrievedArgs[0]);
        }

        public function testCanGetSwitchArgFirstValue()
        {
                // define the options we are expecting
                $expectedOptions = new DefinedSwitches();

                $expectedOptions->addSwitch('fred', 'trout')
                                ->setWithOptionalArg('<fish>', 'which kind of fish you like');

                $parsedSwitches = new ParsedSwitches();
                $parsedSwitches->addSwitch($expectedOptions, 'fred', 'salmon');

                // can we do this?
                $retrievedArg = $parsedSwitches->getFirstArgForSwitch('fred');

                // did it work?
                $this->assertEquals('salmon', $retrievedArg);
        }

        public function testReturnsTrueIfSwitchArgFirstValueMissing()
        {
                // define the options we are expecting
                $expectedOptions = new DefinedSwitches();

                $expectedOptions->addSwitch('fred', 'trout')
                                ->setWithOptionalArg('<fish>', 'which kind of fish you like');

                $parsedSwitches = new ParsedSwitches();
                $parsedSwitches->addSwitch($expectedOptions, 'fred');

                // can we do this?
                $retrievedArg = $parsedSwitches->getFirstArgForSwitch('fred');

                // did it work?
                $this->assertTrue($retrievedArg);
        }

        public function testCanGetAllSwitches()
        {
                // define the options we are expecting
                $expectedOptions = new DefinedSwitches();

                // create the switches to add
                $switchName1 = 'fred';
                $switchDesc1 = 'trout';
                $expectedOptions->addSwitch($switchName1, $switchDesc1);
                $switch1 = $expectedOptions->getSwitchByName($switchName1);

                $switchName2 = 'harry';
                $switchDesc2 = 'salmon';
                $expectedOptions->addSwitch($switchName2, $switchDesc2);
                $switch2 = $expectedOptions->getSwitchByName($switchName2);

                // create the ParsedSwitches object
                $parsedSwitches = new ParsedSwitches($expectedOptions);
                $parsedSwitches->addSwitch($expectedOptions, $switchName1);
                $parsedSwitches->addSwitch($expectedOptions, $switchName2);

                // did it work?
                $switches = $parsedSwitches->getSwitches();
                $this->assertEquals(2, count($switches));
                $this->assertSame($switch1, $switches[$switchName1]->definition);
                $this->assertSame($switch2, $switches[$switchName2]->definition);
        }

        public function testCanAddRepeatedSwitches()
        {
                // define the options we are expecting
                $expectedOptions = new DefinedSwitches();

                // create the switch to add
                $switchName = 'fred';
                $switchDesc = 'trout';
                $expectedOptions->addSwitch($switchName, $switchDesc);

                // create the ParsedSwitches object
                $parsedSwitches = new ParsedSwitches($expectedOptions);
                $this->assertEquals(0, $parsedSwitches->getInvokeCountForSwitch($switchName));

                // repeat the switch
                $parsedSwitches->addSwitch($expectedOptions, $switchName);
                $parsedSwitches->addSwitch($expectedOptions, $switchName);

                // did it work?
                $switches = $parsedSwitches->getSwitches();
                $retrievedArgs = $parsedSwitches->getArgsForSwitch($switchName);
                $this->assertEquals(1, count($switches));
                $this->assertEquals(2, count($retrievedArgs));
                $this->assertEquals(2, $parsedSwitches->getInvokeCountForSwitch($switchName));
        }

        public function testCanAddRepeatedSwitchesWithArguments()
        {
                // define the options we are expecting
                $expectedOptions = new DefinedSwitches();

                // create the switch to add
                $switchName = 'fred';
                $switchDesc = 'trout';
                $expectedOptions->addSwitch($switchName, $switchDesc);

                $args = array
                (
                        'hickory',
                        'dickory',
                        'dock',
                        'the',
                        'mouse',
                        'ran',
                        'up',
                        'the',
                        'clock'
                );

                // add the switch
                $parsedSwitches = new ParsedSwitches();
                foreach ($args as $arg)
                {
                        $parsedSwitches->addSwitch($expectedOptions, $switchName, $arg);
                }

                // did it work?
                $switches = $parsedSwitches->getSwitches();
                $this->assertEquals(1, count($switches));
                $this->assertEquals($switchName, $switches[$switchName]->name);
                $this->assertEquals(count($args), count($parsedSwitches->getArgsForSwitch($switchName)));
                $this->assertEquals(count($args), $parsedSwitches->getInvokeCountForSwitch($switchName));
                $this->assertEquals($args, $parsedSwitches->getArgsForSwitch($switchName));
        }

        public function testCanValidateAllSwitchValuesInOneGo()
        {
                // define the options we are expecting
                $expectedOptions = new DefinedSwitches();
                $switch1 = $expectedOptions->addSwitch('fred', 'desc 1')
                         ->setWithOptionalArg("<fish>", 'the fish that fred likes most')
                         ->setArgValidator(new MustBeString());

                $switch2 = $expectedOptions->addSwitch('harry', 'desc 2')
                         ->setWithOptionalArg('<sauce>', 'the sauce that harry likes most')
                         ->setArgValidator(new MustBeInteger());

                // add the parsed results
                $parsedSwitches = new ParsedSwitches();
                $parsedSwitches->addSwitch($expectedOptions, 'fred', 'trout');
                $parsedSwitches->addSwitch($expectedOptions, 'harry', 'salmon');

                // now, can we validate or not?
                $results = $parsedSwitches->validateSwitchValues();

                // what happened?
                $this->assertTrue(is_array($results));
                $this->assertEquals(1, count($results));
        }
}
