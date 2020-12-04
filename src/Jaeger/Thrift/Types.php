<?php
/*
 * Copyright (c) 2019, The Jaeger Authors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License. You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under the License
 * is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
 * or implied. See the License for the specific language governing permissions and limitations under
 * the License.
 */

namespace Jaeger\Thrift;

use JetBrains\PhpStorm\Pure;

class Types
{
    public const TAG_TYPE_STRING = 0;
    public const TAG_TYPE_DOUBLE = 1;
    public const TAG_TYPE_BOOL = 2;
    public const TAG_TYPE_LONG = 3;
    public const TAG_TYPE_BINARY = 4;

    public const TAG_TYPE_STRINGS = [
        self::TAG_TYPE_STRING => "STRING",
        self::TAG_TYPE_DOUBLE => "DOUBLE",
        self::TAG_TYPE_BOOL => "BOOL",
        self::TAG_TYPE_LONG => "LONG",
        self::TAG_TYPE_BINARY => "BINARY",
    ];

    #[Pure]
    public static function stringToTagType($string): int|string
    {
        $flippedTags = array_flip(self::TAG_TYPE_STRINGS);
        if (array_key_exists($string, $flippedTags)) {
            return $flippedTags[$string];
        }

        return "not a valid TagType string";
    }


    #[Pure]
    public static function tagTypeToString($tagType): string
    {
        if (array_key_exists($tagType, self::TAG_TYPE_STRINGS)) {
            return self::TAG_TYPE_STRINGS[$tagType];
        }

        return "UNSET";
    }
}
