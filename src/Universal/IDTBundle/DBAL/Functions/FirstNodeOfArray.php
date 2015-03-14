<?php

namespace Universal\IDTBundle\DBAL\Functions;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * "FirstNodeOfArray" "(" ArithmeticPrimary ")"
 */
class FirstNodeOfArray extends FunctionNode
{
    public $arrayComma = null;

    /**
     * @override
     * @param Parser $parser
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->arrayComma = $parser->ArithmeticPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * @override
     * @param SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker)
    {// left(denominations, locate(';', denominations)-1)
        return "LEFT(" . $sqlWalker->walkArithmeticPrimary($this->arrayComma) . ", LOCATE(';'," . $sqlWalker->walkArithmeticPrimary($this->arrayComma) . ") -1 )";
    }
}