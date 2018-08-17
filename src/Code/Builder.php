<?php

declare(strict_types=1);

namespace Keboola\HttpExtractor\Code;

use Keboola\Code\Builder as BaseBuilder;
use Keboola\Code\Exception\UserScriptException;

class Builder extends BaseBuilder
{
    protected function strtodate(array $args): string
    {
        if (count($args) !== 2) {
            throw new UserScriptException("Bad argument count for function 'strtodate'!");
        }

        return date($args[1], strtotime($args[0]));
    }
}
