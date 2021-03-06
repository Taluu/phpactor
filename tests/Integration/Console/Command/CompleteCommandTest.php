<?php

namespace Phpactor\Tests\Integration\Console\Command;

use Phpactor\Tests\Integration\SystemTestCase;

class CompleteCommandTest extends SystemTestCase
{
    public function setUp()
    {
        $this->initWorkspace();
        $this->loadProject('Animals');
    }

    /**
     * @dataProvider provideComplete
     */
    public function testComplete($command, $expected)
    {
        $process = $this->phpactor($command);
        $this->assertSuccess($process);
        $this->assertContains($expected, trim($process->getOutput()));
    }

    public function provideComplete()
    {
        return [
            'Complete' => [
                'complete lib/Badger.php 181',
                <<<'EOT'
suggestions:
EOT
            ],
        ];
    }
}

