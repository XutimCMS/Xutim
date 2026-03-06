<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Infra\Doctrine\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * Implementation of PostgreSQL CAST().
 *
 * Converts a value to a specified data type.
 *
 * Adapted from martin-georgiev/postgresql-for-doctrine (MIT License).
 * Copyright (c) 2015-present Martin Georgiev
 *
 * @see https://www.postgresql.org/docs/17/sql-createcast.html
 * @see https://github.com/martin-georgiev/postgresql-for-doctrine
 *
 * @example Using it in DQL: "SELECT CAST(e.value AS VARCHAR) FROM Entity e"
 */
class Cast extends FunctionNode
{
    public Node|string $sourceType;
    public string $targetType;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->sourceType = $parser->SimpleArithmeticExpression();
        $parser->match(TokenType::T_AS);
        $parser->match(TokenType::T_IDENTIFIER);

        $lexer = $parser->getLexer();
        $type = $lexer->token?->value;
        if (!\is_string($type)) {
            return;
        }

        // Handle parameterized types (e.g., DECIMAL(10, 2))
        if ($lexer->isNextToken(TokenType::T_OPEN_PARENTHESIS)) {
            $parser->match(TokenType::T_OPEN_PARENTHESIS);
            $parameter = $parser->Literal();
            $parameters = [$parameter->value];
            while ($lexer->isNextToken(TokenType::T_COMMA)) {
                $parser->match(TokenType::T_COMMA);
                $parameter = $parser->Literal();
                $parameters[] = $parameter->value;
            }

            $parser->match(TokenType::T_CLOSE_PARENTHESIS);
            $type .= '(' . \implode(', ', $parameters) . ')';
        }

        // Handle array types (e.g., TEXT[])
        if ($lexer->lookahead?->value === '[') {
            $parser->match(TokenType::T_NONE);
            if ($lexer->lookahead?->value === ']') {
                $parser->match(TokenType::T_NONE);
                $type .= '[]';
            }
        }

        $this->targetType = $type;

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        $source = $this->sourceType instanceof Node ? $this->sourceType->dispatch($sqlWalker) : $this->sourceType;

        return \sprintf('CAST(%s AS %s)', $source, $this->targetType);
    }
}
