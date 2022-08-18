<?php

namespace Dedoc\ApiDocs\Support\TypeHandlers;

use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;

class PhpDocTypeWalker
{
    public static function traverse(TypeNode $type, array $visitors)
    {
        $callVisitors = function ($node, $method) use ($visitors) {
            foreach ($visitors as $visitor) {
                $visitor->$method($node);
            }
        };

        if ($type instanceof IdentifierTypeNode) {
            $callVisitors($type, 'enter');
            $callVisitors($type, 'leave');
        }

        if ($type instanceof GenericTypeNode) {
            $callVisitors($type, 'enter');
            static::traverse($type->type, $visitors);
            foreach ($type->genericTypes as $genericType) {
                static::traverse($genericType, $visitors);
            }
            $callVisitors($type, 'leave');
        }

        if ($type instanceof ArrayShapeNode) {
            $callVisitors($type, 'enter');
            foreach ($type->items as $itemType) {
                static::traverse($itemType, $visitors);
            }
            $callVisitors($type, 'leave');
        }

        if ($type instanceof ArrayShapeItemNode) {
            $callVisitors($type, 'enter');
            if ($type->keyName) {
                static::traverse($type->keyName, $visitors);
            }
            if ($type->valueType) {
                static::traverse($type->valueType, $visitors);
            }
            $callVisitors($type, 'leave');
        }
    }
}
