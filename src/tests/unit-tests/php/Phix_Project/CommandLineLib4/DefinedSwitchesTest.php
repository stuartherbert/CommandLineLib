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
 * @link        http://www.phix-project.org
 * @version     @@PACKAGE_VERSION@@
 */

namespace Phix_Project\CommandLineLib4;

use Exception;
use PHPUnit_Framework_TestCase;

class DefinedSwitchesTest extends PHPUnit_Framework_TestCase
{
        public function testCanCreateOptions()
        {
                $obj = new DefinedSwitches();
                $this->assertTrue(true);
        }

        public function testCanCreateOptionsWithOneSwitch()
        {
                $switchName = 'help';
                $switchDesc = 'Display this help message';

                $obj = new DefinedSwitches();
                $origSwitch = $obj->newSwitch($switchName, $switchDesc);
                $origSwitch->addShortSwitch('h');

                // did it work?
                $this->assertTrue($obj->testHasSwitchByName($switchName));
        }

        public function testCanRetrieveSwitchByName()
        {
                $switchName = 'help';
                $switchDesc = 'Display this help message';

                $obj = new DefinedSwitches();
                $origSwitch = $obj->newSwitch($switchName, $switchDesc);
                $origSwitch->addShortSwitch('h');

                // did it work?
                $this->assertTrue($obj->testHasSwitchByName($switchName));
                $retrievedSwitch = $obj->getSwitchByName($switchName);
                $this->assertSame($origSwitch, $retrievedSwitch);

                // what happens if we look for a switch that does
                // not exist?
                $notASwitchName = 'version';
                $this->assertFalse($obj->testHasSwitchByName($notASwitchName));
                $caughtException = false;
                try
                {
                        $retrievedSwitch = $obj->getSwitchByName($notASwitchName);
                }
                catch (Exception $e)
                {
                        $caughtException = true;
                }
                $this->assertTrue($caughtException);
        }

        public function testCanRetrieveSwitchByShortSwitch()
        {
                $switchName = 'help';
                $switchDesc = 'Display this help message';

                $obj = new DefinedSwitches();
                $origSwitch = $obj->newSwitch($switchName, $switchDesc);
                $origSwitch->addShortSwitch('h')
                           ->addShortSwitch('?');

                // did it work?
                $this->assertTrue($obj->testHasSwitchByName($switchName));
                $retrievedSwitch1 = $obj->getShortSwitch('h');
                $this->assertSame($origSwitch, $retrievedSwitch1);
                $retrievedSwitch2 = $obj->getShortSwitch('?');
                $this->assertSame($origSwitch, $retrievedSwitch2);
                $this->assertSame($retrievedSwitch1, $retrievedSwitch2);

                // what happens if we try to retrieve a short switch
                // that does not exist?
                $notASwitchName = 'version';
                $this->assertFalse($obj->testHasSwitchByName($notASwitchName));
                $caughtException = false;
                try
                {
                        $retrievedSwitch = $obj->getShortSwitch('v');
                }
                catch (Exception $e)
                {
                        $caughtException = true;
                }
                $this->assertTrue($caughtException);
        }

        public function testCanRetrieveSwitchByLongSwitch()
        {
                $switchName = 'help';
                $switchDesc = 'Display this help message';

                $obj = new DefinedSwitches();
                $origSwitch = $obj->newSwitch($switchName, $switchDesc);
                $origSwitch->addLongSwitch('help')
                           ->addLongSwitch('?');

                // did it work?
                $this->assertTrue($obj->testHasSwitchByName($switchName));
                $retrievedSwitch1 = $obj->getLongSwitch('help');
                $this->assertSame($origSwitch, $retrievedSwitch1);
                $retrievedSwitch2 = $obj->getLongSwitch('?');
                $this->assertSame($origSwitch, $retrievedSwitch2);
                $this->assertSame($retrievedSwitch1, $retrievedSwitch2);

                // what happens if we try to retrieve a long switch
                // that does not exist?
                $notASwitchName = 'version';
                $this->assertFalse($obj->testHasSwitchByName($notASwitchName));
                $caughtException = false;
                try
                {
                        $retrievedSwitch = $obj->getLongSwitch($notASwitchName);
                }
                catch (Exception $e)
                {
                        $caughtException = true;
                }
                $this->assertTrue($caughtException);
        }

        public function testCanRetrieveBothShortAndLongSwitches()
        {
                $switchName = 'help';
                $switchDesc = 'Display this help message';

                $obj = new DefinedSwitches();
                $origSwitch = $obj->newSwitch($switchName, $switchDesc);
                $origSwitch->addShortSwitch('h')
                           ->addShortSwitch('?')
                           ->addLongSwitch('help')
                           ->addLongSwitch('?');

                // did it work?
                $this->assertTrue($obj->testHasSwitchByName($switchName));
                $retrievedSwitch1 = $obj->getShortSwitch('h');
                $this->assertSame($origSwitch, $retrievedSwitch1);
                $retrievedSwitch2 = $obj->getShortSwitch('?');
                $this->assertSame($origSwitch, $retrievedSwitch2);
                $this->assertSame($retrievedSwitch1, $retrievedSwitch2);
                $retrievedSwitch3 = $obj->getLongSwitch('help');
                $this->assertSame($origSwitch, $retrievedSwitch1);
                $retrievedSwitch4 = $obj->getLongSwitch('?');
                $this->assertSame($origSwitch, $retrievedSwitch2);
                $this->assertSame($retrievedSwitch1, $retrievedSwitch2);
                $this->assertSame($retrievedSwitch1, $retrievedSwitch3);
                $this->assertSame($retrievedSwitch1, $retrievedSwitch4);
        }

        public function testCanRetrieveArrayOfAllSwitches()
        {
                $switch1Name = 'help';
                $switch1Desc = 'Display this help message';

                $obj = new DefinedSwitches();
                $switch1 = $obj->newSwitch($switch1Name, $switch1Desc);
                $switch1->addShortSwitch('h')
                        ->addShortSwitch('?')
                        ->addLongSwitch('help')
                        ->addLongSwitch('?');

                $switch2Name = 'version';
                $switch2Desc = 'Display the version number of this app';

                $switch2 = $obj->newSwitch($switch2Name, $switch2Desc);
                $switch2->addShortSwitch('v')
                        ->addShortSwitch('?')
                        ->addLongSwitch('?')
                        ->addLongSwitch('version');

                // did it work?
                $switches = $obj->getSwitches();
                $this->assertTrue(is_array($switches));
                $this->assertEquals(2, count($switches));

                $this->assertTrue(isset($switches[$switch1Name]));
                $this->assertSame($switch1, $switches[$switch1Name]);

                $this->assertTrue(isset($switches[$switch2Name]));
                $this->assertSame($switch2, $switches[$switch2Name]);
        }

        public function testCanRetrieveListOfSwitchesInRightOrderToDisplay()
        {
                $options = new DefinedSwitches();

                $options->newSwitch('version', 'show the version number')
                        ->addShortSwitch('v')
                        ->addLongSwitch('version');

                $options->newSwitch('properties', 'specify the build.properties file to use')
                        ->addShortSwitch('b')
                        ->addLongSwitch('build.properties')
                        ->setRequiredArg('<build.properties>', 'the path to the build.properties file to use')
                        ->setArgHasDefaultValueOf('build.properties');

                $options->newSwitch('packageXml', 'specify the package.xml file to expand')
                        ->addShortSwitch('p')
                        ->addLongSwitch('packageXml')
                        ->setRequiredArg('<package.xml>', 'the path to the package.xml file to use')
                        ->setArgHasDefaultValueOf('.build/package.xml');

                $options->newSwitch('srcFolder', 'specify the src folder to feed into package.xml')
                        ->addShortSwitch('s')
                        ->addLongSwitch('src')
                        ->setRequiredArg('<folder>', 'the path to the folder where the package source files are')
                        ->setArgHasDefaultValueOf('src');

                $options->newSwitch('help', 'displays a summary of how to use this command')
                        ->addShortSwitch('h')
                        ->addShortSwitch('?')
                        ->addLongSwitch('help');

                $options->newSwitch('include', 'adds additional folders to PHP include_path')
                        ->addShortSwitch('I')
                        ->addLongSwitch('include')
                        ->setRequiredArg('<path>', 'path to add to include_path')
                        ->setLongDesc("phix finds all of its commands by searching PHP's include_path for PHP files in "
                                       . "folders called 'PhixCommands'. If you want to phix to look in other folders "
                                       . "without having to add them to PHP's include_path, use --include to tell phix "
                                       . "to look in these folders."
                                       . \PHP_EOL . \PHP_EOL
                                       . "phix expects '<path>' to point to a folder that conforms to the PSR0 standard "
                                       . "for autoloaders."
                                       . \PHP_EOL . \PHP_EOL
                                       . "For example, if your command is the class '\Me\Tools\PhixCommands\ScheduledTask', phix would "
                                       . "expect to autoload this class from the 'Me/Tools/PhixCommands/ScheduledTask.php' file."
                                       . \PHP_EOL . \PHP_EOL
                                       . "If your class lives in the './myApp/lib/Me/Tools/PhixCommands' folder, you would call phix "
                                       . "with 'phix --include=./myApp/lib'");

                $switches = $options->getSwitchesInDisplayOrder();

                // short switches first ...
                //
                // do we have the expected structure back?
                $this->assertTrue(isset($switches['shortSwitchesWithArgs']));
                $this->assertTrue(isset($switches['shortSwitchesWithoutArgs']));

                // has it worked?
                $expectedOrder = array('I', 'b', 'p', 's');
                $actualOrder   = array_keys($switches['shortSwitchesWithArgs']);
                $this->assertEquals($expectedOrder, $actualOrder);

                $expectedOrder = array('?', 'h', 'v');
                $actualOrder   = array_keys($switches['shortSwitchesWithoutArgs']);
                $this->assertEquals($expectedOrder, $actualOrder);

                // then long switches
                //
                // do we have the expected structure back?
                $this->assertTrue(isset($switches['longSwitchesWithArgs']));
                $this->assertTrue(isset($switches['longSwitchesWithoutArgs']));

                // has it worked?
                $expectedOrder = array('build.properties', 'include', 'packageXml', 'src');
                $actualOrder   = array_keys($switches['longSwitchesWithArgs']);
                $this->assertEquals($expectedOrder, $actualOrder);

                $expectedOrder = array('help', 'version');
                $actualOrder   = array_keys($switches['longSwitchesWithoutArgs']);
                $this->assertEquals($expectedOrder, $actualOrder);

                // finally, the list of all switches
                //
                // do we have the expected structure back?
                $this->assertTrue(isset($switches['allSwitches']));

                // has it worked?
                $expectedOrder = array('-?', '-I', '-b', '-h', '-p', '-s', '-v', '--build.properties', '--help', '--include', '--packageXml', '--src', '--version');
                $actualOrder   = array_keys($switches['allSwitches']);
                $this->assertEquals($expectedOrder, $actualOrder);
        }
}
