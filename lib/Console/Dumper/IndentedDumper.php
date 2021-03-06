<?php

namespace Phpactor\Console\Dumper;

use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Console\Dumper\Dumper;

final class IndentedDumper implements Dumper
{
    const PADDING = '  ';

    public function dump(OutputInterface $output, array $data)
    {
        $this->doDump($output, $data);
    }

    private function doDump(OutputInterface $output, array $data, $padding = 0)
    {
        switch ($padding) {
            case 1:
                $style = 'info';
                break;
            default:
                $style = 'comment';
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $output->writeln(sprintf('%s<%s>%s</>:', str_repeat(self::PADDING, $padding), $style, $key));
                $this->doDump($output, $value, ++$padding);
                $padding--;
                continue;
            }

            $output->writeln(sprintf(
                '%s<%s>%s</>:%s',
                str_repeat(self::PADDING, $padding),
                $style,
                $key,
                $value
            ));
        }
    }
}
