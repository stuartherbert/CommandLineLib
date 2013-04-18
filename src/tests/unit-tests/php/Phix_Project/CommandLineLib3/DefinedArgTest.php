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

namespace Phix_Project\CommandLineLib3;

use PHPUnit_Framework_TestCase;

use Phix_Project\ValidationLib4\File_MustBeValidFile;
use Phix_Project\ValidationLib4\File_MustBeValidPath;
use Phix_Project\ValidationLib4\File_MustBeWriteable;
use Phix_Project\ValidationLib4\ValidationResult;

class DefinedArgTest extends PHPUnit_Framework_TestCase
{
        public function testCanCreate()
        {
                $name = '<command>';
                $desc = 'The <command> you need help with';

                $obj = new DefinedArg($name, $desc);

                // did it work?
                $this->assertEquals($name, $obj->name);
                $this->assertEquals($desc, $obj->desc);
                $this->assertTrue($obj->testIsOptional());
                $this->assertFalse($obj->testIsRequired());
        }

        public function testCanCreateOptionalArg()
        {
                $name = '<command>';
                $desc = 'The <command> you need help with';

                $obj = new DefinedArg($name, $desc);
                $obj->setIsOptional();

                // did it work?
                $this->assertEquals($name, $obj->name);
                $this->assertEquals($desc, $obj->desc);
                $this->assertTrue($obj->testIsOptional());
                $this->assertFalse($obj->testIsRequired());
        }

        public function testCanCreateRequiredArg()
        {
                $name = '<command>';
                $desc = 'The <command> you need help with';

                $obj = new DefinedArg($name, $desc);
                $obj->setIsRequired();

                // did it work?
                $this->assertEquals($name, $obj->name);
                $this->assertEquals($desc, $obj->desc);
                $this->assertTrue($obj->testIsRequired());
                $this->assertFalse($obj->testIsOptional());
        }

        public function testCanRequireAValidFile()
        {
                $name = '<command>';
                $desc = 'The <command> you need help with';

                $obj = new DefinedArg($name, $desc);
                $obj->setValidator(new File_MustBeValidFile());

                // did it work?
                $this->assertEquals($name, $obj->name);
                $this->assertEquals($desc, $obj->desc);
                $this->assertTrue($obj->testIsOptional());
                $this->assertTrue($obj->testMustValidateWith('Phix_Project\ValidationLib4\File_MustBeValidFile'));
        }

        public function testCanRequireAValidPath()
        {
                $name = '<command>';
                $desc = 'The <command> you need help with';

                $obj = new DefinedArg($name, $desc);
                $obj->setValidator(new File_MustBeValidPath());

                // did it work?
                $this->assertEquals($name, $obj->name);
                $this->assertEquals($desc, $obj->desc);
                $this->assertTrue($obj->testIsOptional());
                $this->assertTrue($obj->testMustValidateWith('Phix_Project\ValidationLib4\File_MustBeValidPath'));
        }

        public function testCanRequireWriteableFileArg()
        {
                $name = '<command>';
                $desc = 'The <command> you need help with';

                $obj = new DefinedArg($name, $desc);
                $obj->setValidator(new File_MustBeWriteable());

                // did it work?
                $this->assertEquals($name, $obj->name);
                $this->assertEquals($desc, $obj->desc);
                $this->assertTrue($obj->testIsOptional());
                $this->assertTrue($obj->testMustValidateWith('Phix_Project\ValidationLib4\File_MustBeWriteable'));
        }

        public function testCanSetDefaultValueForArg()
        {
                $name = '<command>';
                $desc = 'The <command> you need help with';

                $obj = new DefinedArg($name, $desc);
                $obj->setDefaultValue('help');

                // did it work?
                $this->assertEquals($name, $obj->name);
                $this->assertEquals($desc, $obj->desc);
                $this->assertEquals('help', $obj->defaultValue);
        }

        public function testCanCheckToSeeIfAnArgMustValidateWithANamedValidator()
        {
                $name = '<command>';
                $desc = 'The <command> you need help with';

                $obj = new DefinedArg($name, $desc);
                $obj->setValidator(new File_MustBeWriteable());

                // did it work?
                $this->assertEquals($name, $obj->name);
                $this->assertEquals($desc, $obj->desc);
                $this->assertTrue($obj->testIsOptional());
                $this->assertTrue($obj->testMustValidateWith('Phix_Project\ValidationLib4\File_MustBeWriteable'));
                $this->assertFalse($obj->testMustValidateWith('Phix_Project\ValidationLib4\File_MustBeValidFile'));
        }

        public function testAnExceptionIsNotThrownIfValidatorClassDoesNotExist()
        {
                $name = '<command>';
                $desc = 'The <command> you need help with';

                $obj = new DefinedArg($name, $desc);
                $obj->setValidator(new File_MustBeWriteable());

                // did it work?
                $this->assertEquals($name, $obj->name);
                $this->assertEquals($desc, $obj->desc);
                $this->assertTrue($obj->testIsOptional());
                $this->assertFalse($obj->testMustValidateWith('Phix_Project\ValidationLib4\File_MustNotBeWriteable'));
        }

        public function testCanValidateAnArgsValue()
        {
                $name = '<command>';
                $desc = 'The <command> you need help with';

                $obj = new DefinedArg($name, $desc);
                $obj->setValidator(new File_MustBeValidFile());

                $this->assertEquals($name, $obj->name);
                $this->assertEquals($desc, $obj->desc);
                $this->assertTrue($obj->testIsOptional());
                $this->assertTrue($obj->testMustValidateWith('Phix_Project\ValidationLib4\File_MustBeValidFile'));

                // now, validate the data
                $return = $obj->testIsValid(__FILE__);
                $this->assertTrue($return instanceof ValidationResult);
                $this->assertEquals(0, count($return->getErrors()));
        }

        public function testGetsErrorMessagesWhenValidationFails()
        {
                $name = '<command>';
                $desc = 'The <command> you need help with';

                $obj = new DefinedArg($name, $desc);
                $obj->setValidator(new File_MustBeValidPath());

                $this->assertEquals($name, $obj->name);
                $this->assertEquals($desc, $obj->desc);
                $this->assertTrue($obj->testIsOptional());
                $this->assertTrue($obj->testMustValidateWith('Phix_Project\ValidationLib4\File_MustBeValidPath'));

                // now, validate the data
                $return = $obj->testIsValid(__FILE__);
                $this->assertTrue($return instanceof ValidationResult);
                $this->assertEquals(1, count($return->getErrors()));

                // we do not need to test the actual error message here
                // as the error message may change in future releases
                //
                // simply having an error message is enough
        }
}
