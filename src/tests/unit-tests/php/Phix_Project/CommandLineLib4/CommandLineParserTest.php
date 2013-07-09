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

use Phix_Project\ValidationLib4\File_MustBeValidFile;
use Phix_Project\ValidationLib4\File_MustBeWriteable;
use Phix_Project\ValidationLib4\File_MustBeValidPath;

class CommandLineParserTest extends PHPUnit_Framework_TestCase
{
        /**
         *
         * @return DefinedSwitches
         */
        protected function setupOptions()
        {
                $options = new DefinedSwitches();

                $options->newSwitch('shortHelp', 'display this help message')
                        ->addShortSwitch('h')
                        ->addShortSwitch('?');

                $options->newSwitch('longHelp', 'display full help message')
                        ->addLongSwitch('help')
                        ->addLongSwitch('?');

                $options->newSwitch('version', 'display app version number')
                        ->addShortSwitch('v')
                        ->addLongSwitch('version');

                $options->newSwitch('include', 'add a folder to search within')
                        ->addShortSwitch('I')
                        ->addLongSwitch('include')
                        ->setRequiredArg('<path>', 'path to the folder to search');

                $options->newSwitch('library', 'add a library to link against')
                        ->addShortSwitch('l')
                        ->addLongSwitch('lib')
                        ->setRequiredArg('<lib>', 'the name of a library to link against')
                        ->setSwitchIsRepeatable();

                $options->newSwitch('srcFolder', 'add a folder to load source code from')
                        ->addShortSwitch('s')
                        ->addLongSwitch('srcFolder')
                        ->setRequiredArg('<srcFolder>', 'path to the folder to load source code from')
                        ->setArgHasDefaultValueOf('/usr/this-does-not-exist');

                $options->newSwitch('warnings', 'enable warnings')
                        ->addShortSwitch('W')
                        ->addLongSwitch('warnings')
                        ->setOptionalArg('<warnings>', 'comma-separated list of warnings to enable')
                        ->setArgHasDefaultValueOf('all')
                        ->setSwitchIsRepeatable();

                return $options;
        }

        public function testCanCreate()
        {
                $obj = new CommandLineParser();
                $this->assertTrue(true);
        }

        public function testCanParseZeroArgs()
        {
                $options = $this->setupOptions();
                $this->assertTrue($options instanceof DefinedSwitches);

                $argv = array();

                $parser = new CommandLineParser();
                $parsed = $parser->parseCommandLine($argv, 0, $options);

                // did it work?
                $this->assertTrue ($parsed instanceof ParsedCommandLine);
                $this->assertTrue (is_array($parsed->switches));

                // do we have the right number of left-over args?
                $this->assertTrue (is_array($parsed->args));
                $this->assertEquals(array(), $parsed->args);

                // do we have the right number of switches?
                // we should have just the switches with default values
                $switches = $parsed->switches;
                $this->assertEquals(2, count($switches));
                $this->assertTrue(isset($switches['srcFolder']));
                $this->assertEquals('srcFolder', $switches['srcFolder']->name);
                $this->assertTrue($switches['srcFolder']->testIsDefaultValue());
                $this->assertTrue(isset($switches['warnings']));
                $this->assertEquals('warnings', $switches['warnings']->name);
                $this->assertTrue($switches['warnings']->testIsDefaultValue());
        }

        public function testCanParseShortSwitches()
        {
                $options = $this->setupOptions();
                $this->assertTrue($options instanceof DefinedSwitches);

                $argv = array
                (
                        'PhixTest',
                        '-vh',
                        'help'
                );

                $parser = new CommandLineParser();
                $parsed = $parser->parseCommandLine($argv, 1, $options);

                // did it work?
                $this->assertTrue ($parsed instanceof ParsedCommandLine);

                // do we have the right number of left-over args?
                $this->assertTrue (is_array($parsed->args));
                $this->assertEquals(array("help"), $parsed->args);

                $switches = $parsed->switches;
                $this->assertEquals(4, count($switches));
                $this->assertTrue(isset($switches['version']));
                $this->assertEquals('version', $switches['version']->name);
                $this->assertTrue($switches['version']->values[0]);
                $this->assertTrue(isset($switches['shortHelp']));
                $this->assertEquals('shortHelp', $switches['shortHelp']->name);
                $this->assertTrue($switches['shortHelp']->values[0]);
                $this->assertTrue(isset($switches['srcFolder']));
                $this->assertEquals('srcFolder', $switches['srcFolder']->name);
                $this->assertEquals('/usr/this-does-not-exist', $switches['srcFolder']->values[0]);
                $this->assertTrue($switches['srcFolder']->testIsDefaultValue());
                $this->assertTrue(isset($switches['warnings']));
                $this->assertEquals('warnings', $switches['warnings']->name);
                $this->assertEquals('all', $switches['warnings']->values[0]);
                $this->assertTrue($switches['warnings']->testIsDefaultValue());
        }

        public function testCanParseShortSwitchWithArg()
        {
                $options = $this->setupOptions();
                $this->assertTrue($options instanceof DefinedSwitches);

                $argv = array
                (
                        'PhixTest',
                        '-I',
                        '/tmp',
                        'help'
                );

                $parser = new CommandLineParser();
                $parsed = $parser->parseCommandLine($argv, 1, $options);

                // did it work?
                $this->assertTrue ($parsed instanceof ParsedCommandLine);

                // do we have the right number of left-over args?
                $this->assertTrue (is_array($parsed->args));
                $this->assertEquals(array("help"), $parsed->args);

                $switches = $parsed->switches;
                $this->assertTrue(isset($switches['include']));
                $this->assertEquals('include', $switches['include']->name);
                $this->assertEquals('/tmp', $switches['include']->getFirstValue());
        }

        public function testCanParseShortSwitchWithEmbeddedArg()
        {
                $options = $this->setupOptions();
                $this->assertTrue($options instanceof DefinedSwitches);

                $argv = array
                (
                        'PhixTest',
                        '-I/tmp',
                        'help'
                );

                $parser = new CommandLineParser();
                $parsed = $parser->parseCommandLine($argv, 1, $options);

                // did it work?
                $this->assertTrue ($parsed instanceof ParsedCommandLine);

                // do we have the right number of left-over args?
                $this->assertTrue (is_array($parsed->args));
                $this->assertEquals(array("help"), $parsed->args);

                $switches = $parsed->switches;
                $this->assertTrue(isset($switches['include']));
                $this->assertEquals('include', $switches['include']->name);
                $this->assertEquals('/tmp', $switches['include']->getFirstValue());
        }

        public function testCanParseLongSwitches()
        {
                $options = $this->setupOptions();
                $this->assertTrue($options instanceof DefinedSwitches);

                $argv = array
                (
                        'PhixTest',
                        '--version',
                        '--help',
                        'help'
                );

                $parser = new CommandLineParser();
                $parsed = $parser->parseCommandLine($argv, 1, $options);

                // did it work?
                $this->assertTrue ($parsed instanceof ParsedCommandLine);

                // do we have the right number of left-over args?
                $this->assertTrue (is_array($parsed->args));
                $this->assertEquals(array("help"), $parsed->args);

                // do we have our long switches?
                $switches = $parsed->switches;
                $this->assertTrue(isset($switches['version']));
                $this->assertEquals('version', $switches['version']->name);
                $this->assertTrue(isset($switches['longHelp']));
                $this->assertEquals('longHelp', $switches['longHelp']->name);
        }

        public function testCanParseLongSwitchWithArgument()
        {
                $options = $this->setupOptions();
                $this->assertTrue($options instanceof DefinedSwitches);

                $argv = array
                (
                        'PhixTest',
                        '--include',
                        '/tmp',
                        'help'
                );

                $parser = new CommandLineParser();
                $parsed = $parser->parseCommandLine($argv, 1, $options);

                // did it work?
                $this->assertTrue ($parsed instanceof ParsedCommandLine);

                // do we have the right number of left-over args?
                $this->assertTrue (is_array($parsed->args));
                $this->assertEquals(array("help"), $parsed->args);

                // do we have the right number of switches?
                // (1 supplied + 2 defaults)
                $switches = $parsed->switches;
                $this->assertEquals(3, count($switches));

                $this->assertTrue(isset($switches['include']));
                $this->assertEquals('include', $switches['include']->name);
                $this->assertEquals('/tmp', $switches['include']->getFirstValue());
        }

        public function testCanParseLongSwitchWithEmbeddedArgument()
        {
                $options = $this->setupOptions();
                $this->assertTrue($options instanceof DefinedSwitches);

                $argv = array
                (
                        'PhixTest',
                        '--include=/tmp',
                        'help'
                );

                $parser = new CommandLineParser();
                $parsed = $parser->parseCommandLine($argv, 1, $options);

                // did it work?
                $this->assertTrue ($parsed instanceof ParsedCommandLine);

                // do we have the right number of left-over args?
                $this->assertTrue (is_array($parsed->args));
                $this->assertEquals(array("help"), $parsed->args);

                $switches = $parsed->switches;
                $this->assertEquals(3, count($switches));
                $this->assertTrue(isset($switches['include']));
                $this->assertEquals('include', $switches['include']->name);
                $this->assertEquals('/tmp', $switches['include']->getFirstValue());
        }

        public function testCanparseCommandLineThatRepeat()
        {
                $options = $this->setupOptions();
                $this->assertTrue($options instanceof DefinedSwitches);

                $argv = array
                (
                        'PhixTest',
                        '-lfred',
                        '--include',
                        '/tmp',
                        '--lib=harry',
                        'help'
                );

                $parser = new CommandLineParser();
                $parsed = $parser->parseCommandLine($argv, 1, $options);

                // did it work?
                $this->assertTrue ($parsed instanceof ParsedCommandLine);

                // do we have the right number of left-over args?
                $this->assertTrue (is_array($parsed->args));
                $this->assertEquals(array("help"), $parsed->args);

                // did we get the right number of switches?
                $switches = $parsed->switches;
                $this->assertEquals(4, count($switches));

                // did we get the include switch?
                $this->assertTrue(isset($switches['include']));
                $this->assertEquals('include', $switches['include']->name);
                $this->assertEquals('/tmp', $switches['include']->getFirstValue());

                // did we get both library switches?
                $this->assertTrue(isset($switches['library']));
                $this->assertEquals('library', $switches['library']->name);
                $this->assertEquals('fred', $switches['library']->values[0]);
                $this->assertEquals('harry', $switches['library']->values[1]);
        }

        public function testCanTellShortAndLongSwitchesApart()
        {
                $options = $this->setupOptions();
                $this->assertTrue($options instanceof DefinedSwitches);

                $argv = array
                (
                        'PhixTest',
                        '-?',
                        '--?',
                        'help'
                );

                $parser = new CommandLineParser();
                $parsed = $parser->parseCommandLine($argv, 1, $options);

                // did it work?
                $this->assertTrue ($parsed instanceof ParsedCommandLine);

                // do we have the right number of left-over args?
                $this->assertTrue (is_array($parsed->args));
                $this->assertEquals(array("help"), $parsed->args);

                // did we get the number of switches we expected?
                // (2 supplied + 2 default)
                $switches = $parsed->switches;
                $this->assertEquals(4, count($switches));

                // did we get the include switch?
                $this->assertTrue(isset($switches['shortHelp']));
                $this->assertEquals('shortHelp', $switches['shortHelp']->name);
                $this->assertTrue(isset($switches['longHelp']));
                $this->assertEquals('longHelp', $switches['longHelp']->name);
        }

        public function testParserStopsOnDoubleDash()
        {
                $options = $this->setupOptions();
                $this->assertTrue($options instanceof DefinedSwitches);

                $argv = array
                (
                        'PhixTest',
                        '-vh',
                        '--',
                        'help'
                );

                $parser = new CommandLineParser();
                $parsed = $parser->parseCommandLine($argv, 1, $options);

                // did it work?
                $this->assertTrue ($parsed instanceof ParsedCommandLine);

                // do we have the right number of left-over args?
                $this->assertTrue (is_array($parsed->args));
                $this->assertEquals(array("help"), $parsed->args);

                // did we get the number of switches expected?
                $switches = $parsed->switches;
                $this->assertEquals(4, count($switches));

                $this->assertTrue(isset($switches['version']));
                $this->assertEquals('version', $switches['version']->name);
                $this->assertTrue($switches['version']->values[0]);
                $this->assertTrue(isset($switches['shortHelp']));
                $this->assertEquals('shortHelp', $switches['shortHelp']->name);
                $this->assertTrue($switches['shortHelp']->values[0]);
                $this->assertTrue(isset($switches['srcFolder']));
                $this->assertEquals('srcFolder', $switches['srcFolder']->name);
                $this->assertEquals('/usr/this-does-not-exist', $switches['srcFolder']->values[0]);
                $this->assertTrue($switches['srcFolder']->testIsDefaultValue());
                $this->assertTrue(isset($switches['warnings']));
                $this->assertEquals('warnings', $switches['warnings']->name);
                $this->assertEquals('all', $switches['warnings']->values[0]);
                $this->assertTrue($switches['warnings']->testIsDefaultValue());
        }

        public function testParserThrowsExceptionWhenUnexpectedShortSwitch()
        {
                $options = $this->setupOptions();
                $this->assertTrue($options instanceof DefinedSwitches);

                $argv = array
                (
                        'PhixTest',
                        '-vhx',
                        'help'
                );

                $caughtException = false;
                try
                {
                        $parser = new CommandLineParser();
                        $parsed = $parser->parseCommandLine($argv, 1, $options);
                }
                catch (Exception $e)
                {
                        $caughtException = true;
                }

                // did it work?
                $this->assertTrue ($caughtException);
        }

        public function testParserThrowsExceptionWhenUnexpectedLongSwitch()
        {
                $options = $this->setupOptions();
                $this->assertTrue($options instanceof DefinedSwitches);

                $argv = array
                (
                        'PhixTest',
                        '--panic',
                        'help'
                );

                $caughtException = false;
                try
                {
                        $parser = new CommandLineParser();
                        $parsed = $parser->parseCommandLine($argv, 1, $options);
                }
                catch (Exception $e)
                {
                        $caughtException = true;
                }

                // did it work?
                $this->assertTrue ($caughtException);
        }

        public function testParserThrowsExceptionWhenShortSwitchMissingArg()
        {
                $options = $this->setupOptions();
                $this->assertTrue($options instanceof DefinedSwitches);

                $argv = array
                (
                        'PhixTest',
                        '-I'
                );

                $caughtException = false;
                try
                {
                        $parser = new CommandLineParser();
                        $parsed = $parser->parseCommandLine($argv, 1, $options);
                }
                catch (Exception $e)
                {
                        $caughtException = true;
                        $this->assertEquals("switch -I expected argument", $e->getMessage());
                }

                // did it work?
                $this->assertTrue ($caughtException);
        }

        public function testParserThrowsExceptionWhenLongSwitchMissingArg()
        {
                $options = $this->setupOptions();
                $this->assertTrue($options instanceof DefinedSwitches);

                $argv = array
                (
                        'PhixTest',
                        '--include'
                );

                $caughtException = false;
                try
                {
                        $parser = new CommandLineParser();
                        $parsed = $parser->parseCommandLine($argv, 1, $options);
                }
                catch (Exception $e)
                {
                        $caughtException = true;
                        $this->assertEquals("switch --include expected argument", $e->getMessage());
                }

                // did it work?
                $this->assertTrue ($caughtException);

                // there is more than one way to leave out an argument
                $options = $this->setupOptions();
                $this->assertTrue($options instanceof DefinedSwitches);

                $argv = array
                (
                        'PhixTest',
                        '--include='
                );

                $caughtException = false;
                try
                {
                        $parser = new CommandLineParser();
                        $parsed = $parser->parseCommandLine($argv, 1, $options);
                }
                catch (Exception $e)
                {
                        $caughtException = true;
                        $this->assertEquals("switch --include expected argument", $e->getMessage());
                }

                // did it work?
                $this->assertTrue ($caughtException);
        }

        public function testParserThrowsExceptionWhenSwitchInMiddleMissingArg()
        {
                $options = $this->setupOptions();
                $this->assertTrue($options instanceof DefinedSwitches);

                $argv = array
                (
                        'PhixTest',
                        '-vIh'
                );

                $caughtException = false;
                try
                {
                        $parser = new CommandLineParser();
                        $parsed = $parser->parseCommandLine($argv, 1, $options);
                }
                catch (Exception $e)
                {
                        $caughtException = true;
                        $this->assertEquals("switch -I expected argument", $e->getMessage());
                }

                // did it work?
                $this->assertTrue ($caughtException);
        }

        public function testCanLumpShortSwitchesTogetherWithLastOneRequiringAnArgument()
        {
                $options = $this->setupOptions();
                $this->assertTrue($options instanceof DefinedSwitches);

                $argv = array
                (
                        'PhixTest',
                        '-vhI',
                        '/fred'
                );

                $parser = new CommandLineParser();
                $parsed = $parser->parseCommandLine($argv, 1, $options);

                // did it work?
                $this->assertTrue ($parsed instanceof ParsedCommandLine);

                // do we have the right number of left-over args?
                $this->assertTrue (is_array($parsed->args));
                $this->assertEquals(array(), $parsed->args);

                // did we get the right number of switches?
                $switches = $parsed->switches;
                $this->assertEquals(5, count($switches));

                // did we get the switches we expected?
                $this->assertTrue(isset($switches['version']));
                $this->assertEquals('version', $switches['version']->name);
                $this->assertTrue($switches['version']->values[0]);
                $this->assertTrue(isset($switches['shortHelp']));
                $this->assertEquals('shortHelp', $switches['shortHelp']->name);
                $this->assertTrue($switches['shortHelp']->values[0]);
                $this->assertTrue(isset($switches['include']));
                $this->assertEquals('include', $switches['include']->name);
                $this->assertEquals('/fred', $switches['include']->values[0]);

                // don't forget the switches with default values
                $this->assertTrue(isset($switches['include']));
                $this->assertEquals('srcFolder', $switches['srcFolder']->name);
                $this->assertEquals('/usr/this-does-not-exist', $switches['srcFolder']->values[0]);
                $this->assertTrue($switches['srcFolder']->testIsDefaultValue());
                $this->assertEquals('warnings', $switches['warnings']->name);
                $this->assertEquals('all', $switches['warnings']->values[0]);
                $this->assertTrue($switches['warnings']->testIsDefaultValue());
        }

        public function testCanLumpShortSwitchesTogetherWithLastOneHavingAOptionalArgument()
        {
                $options = $this->setupOptions();
                $this->assertTrue($options instanceof DefinedSwitches);

                $argv = array
                (
                        'PhixTest',
                        '-vhW',
                );

                $parser = new CommandLineParser();
                $parsed = $parser->parseCommandLine($argv, 1, $options);

                // did it work?
                $this->assertTrue ($parsed instanceof ParsedCommandLine);

                // do we have the right number of left-over args?
                $this->assertTrue (is_array($parsed->args));
                $this->assertEquals(array(), $parsed->args);

                // did we get the right number of switches?
                $switches = $parsed->switches;
                $this->assertEquals(4, count($switches));

                // did we get the switches we expected?
                $this->assertTrue(isset($switches['version']));
                $this->assertEquals('version', $switches['version']->name);
                $this->assertTrue($switches['version']->values[0]);
                $this->assertTrue(isset($switches['shortHelp']));
                $this->assertEquals('shortHelp', $switches['shortHelp']->name);
                $this->assertTrue($switches['shortHelp']->values[0]);
                $this->assertTrue(isset($switches['warnings']));
                $this->assertEquals('warnings', $switches['warnings']->name);
                $this->assertEquals('all', $switches['warnings']->values[0]);

                // don't forget the default values
                $this->assertTrue(isset($switches['srcFolder']));
                $this->assertEquals('srcFolder', $switches['srcFolder']->name);
                $this->assertEquals('/usr/this-does-not-exist', $switches['srcFolder']->values[0]);
                $this->assertTrue($switches['srcFolder']->testIsDefaultValue());
        }

        public function testSwitchesCanHaveOptionalArgs()
        {
                $options = $this->setupOptions();
                $this->assertTrue($options instanceof DefinedSwitches);

                $argv = array
                (
                        'PhixTest',
                        '--warnings',
                );

                $parser = new CommandLineParser();
                $parsed = $parser->parseCommandLine($argv, 1, $options);

                // did it work?
                $this->assertTrue ($parsed instanceof ParsedCommandLine);

                // do we have the right number of left-over args?
                $this->assertTrue (is_array($parsed->args));
                $this->assertEquals(array(), $parsed->args);

                // did we get the number of switches we expected?
                $switches = $parsed->switches;
                $this->assertEquals(2, count($switches));

                // do we have the warnings switch?
                $this->assertTrue(isset($switches['warnings']));
                $this->assertEquals('warnings', $switches['warnings']->name);
                $this->assertEquals('all', $switches['warnings']->getFirstValue());
        }

        public function testOptionalArgsCanHaveValues()
        {
                $options = $this->setupOptions();
                $this->assertTrue($options instanceof DefinedSwitches);

                $argv = array
                (
                        'PhixTest',
                        '--warnings=all',
                );

                $parser = new CommandLineParser();
                $parsed = $parser->parseCommandLine($argv, 1, $options);

                // did it work?
                $this->assertTrue ($parsed instanceof ParsedCommandLine);

                // do we have the right number of left-over args?
                $this->assertTrue (is_array($parsed->args));
                $this->assertEquals(array(), $parsed->args);

                // did we get the number of switches that we expected?
                $switches = $parsed->switches;
                $this->assertEquals(2, count($switches));

                // do we have the warnings switch?
                $this->assertTrue(isset($switches['warnings']));
                $this->assertEquals('warnings', $switches['warnings']->name);
                $this->assertEquals('all', $switches['warnings']->getFirstValue());
        }

        public function testDefaultValuesAreAddedIfSwitchNotSeen()
        {
                $options = $this->setupOptions();
                $this->assertTrue($options instanceof DefinedSwitches);

                $argv = array
                (
                        'PhixTest'
                );

                $parser = new CommandLineParser();
                $parsed = $parser->parseCommandLine($argv, 1, $options);

                // did it work?
                $this->assertTrue ($parsed instanceof ParsedCommandLine);

                // do we have the right number of left-over args?
                $this->assertTrue (is_array($parsed->args));
                $this->assertEquals(array(), $parsed->args);

                // what defaults have leeched through?
                // it should only be the required switches that have
                // default values

                $switches = $parsed->switches;
                $this->assertTrue(is_array($switches));
                $this->assertEquals(2, count($switches));
                $this->assertTrue(isset($switches['srcFolder']));
                $this->assertEquals('srcFolder', $switches['srcFolder']->name);
                $this->assertTrue(isset($switches['warnings']));
                $this->assertEquals('warnings', $switches['warnings']->name);
        }

        public function testDefaultValuesAreAddedIfNoSwitchesPresent()
        {
                // this is a bug I first discovered in Phix, and here
                // is the code necessary to reproduce the faults
                $options = new DefinedSwitches();

                $options->newSwitch('properties', 'specify the build.properties file to use')
                        ->addShortSwitch('b')
                        ->addLongSwitch('build.properties')
                        ->setRequiredArg('<build.properties>', 'the path to the build.properties file to use')
                        ->setArgHasDefaultValueOf('build.properties')
                        ->setArgValidator(new File_MustBeValidFile());

                $options->newSwitch('packageXml', 'specify the package.xml file to expand')
                        ->addShortSwitch('p')
                        ->addLongSwitch('packageXml')
                        ->setRequiredArg('<package.xml>', 'the path to the package.xml file to use')
                        ->setArgHasDefaultValueOf('.build/package.xml')
                        ->setArgValidator(new File_MustBeValidFile())
                        ->setArgValidator(new File_MustBeWriteable());

                $options->newSwitch('srcFolder', 'specify the src folder to feed into package.xml')
                        ->addShortSwitch('s')
                        ->addLongSwitch('src')
                        ->setRequiredArg('<folder>', 'the path to the folder where the package source files are')
                        ->setArgHasDefaultValueOf('src')
                        ->setArgValidator(new File_MustBeValidPath());

                $argv = array
                (
                        './Phix',
                        'pear:expand-package-xml'
                );

                $parser = new CommandLineParser();
                $parsed = $parser->parseCommandLine($argv, 2, $options);

                // did it work?
                $this->assertTrue ($parsed instanceof ParsedCommandLine);

                // do we have the right number of left-over args?
                $this->assertTrue (is_array($parsed->args));
                $this->assertEquals(array(), $parsed->args);

                // are the defaults present?
                $switches = $parsed->switches;
                $this->assertTrue(is_array($switches));
                $this->assertEquals(3, count($switches));
                $this->assertTrue(isset($switches['properties']));
                $this->assertEquals('properties', $switches['properties']->name);
                $this->assertTrue(isset($switches['packageXml']));
                $this->assertEquals('packageXml', $switches['packageXml']->name);
                $this->assertTrue(isset($switches['srcFolder']));
                $this->assertEquals('srcFolder', $switches['srcFolder']->name);
        }
}
