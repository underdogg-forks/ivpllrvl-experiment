1~
<?php
2~

3~
namespace Tests\Unit\Support;
4~

5~
use Modules\Core\Support\DiacriticsHelper;
6~
use PHPUnit\Framework\Attributes\CoversClass;
7~
use PHPUnit\Framework\Attributes\DataProvider;
8~
use PHPUnit\Framework\Attributes\Test;
9~
use Tests\Unit\UnitTestCase;
10~

11~
#[CoversClass(DiacriticsHelper::class)]
12~
class DiacriticsHelperTest extends UnitTestCase
13~
{
14~
    #[Test]
15~
    public function itDetectsAsciiStringAsUtf8(): void
16~
    {
17~
        $result = DiacriticsHelper::diacritics_seems_utf8('hello world');
18~
        
19~
        $this->assertTrue($result);
20~
    }
21~

22~
    #[Test]
23~
    public function itDetectsUtf8String(): void
24~
    {
25~
        $result = DiacriticsHelper::diacritics_seems_utf8('Héllo Wörld');
26~
        
27~
        $this->assertTrue($result);
28~
    }
29~

30~
    #[Test]
31~
    public function itDetectsNonUtf8String(): void
32~
    {
33~
        // Create an invalid UTF-8 sequence
34~
        $invalidUtf8 = "\x80\x81\x82";
35~
        
36~
        $result = DiacriticsHelper::diacritics_seems_utf8($invalidUtf8);
37~
        
38~
        $this->assertFalse($result);
39~
    }
40~

41~
    #[Test]
42~
    public function itRemovesAccentsFromLatinCharacters(): void
43~
    {
44~
        $input = 'Café';
45~
        
46~
        $result = DiacriticsHelper::diacritics_remove_accents($input);
47~
        
48~
        $this->assertStringContainsString('Caf', $result);
49~
    }
50~

51~
    #[Test]
52~
    public function itPreservesAsciiCharacters(): void
53~
    {
54~
        $input = 'Hello World 123';
55~
        
56~
        $result = DiacriticsHelper::diacritics_remove_accents($input);
57~
        
58~
        $this->assertSame($input, $result);
59~
    }
60~

61~
    #[Test]
62~
    public function itHandlesEmptyString(): void
63~
    {
64~
        $result = DiacriticsHelper::diacritics_remove_accents('');
65~
        
66~
        $this->assertSame('', $result);
67~
    }
68~

69~
    #[Test]
70~
    #[DataProvider('accentProvider')]
71~
    public function itRemovesVariousAccents(string $input, string $expected): void
72~
    {
73~
        $result = DiacriticsHelper::diacritics_remove_accents($input);
74~
        
75~
        $this->assertStringContainsString($expected, $result);
76~
    }
77~

78~
    public static function accentProvider(): array
79~
    {
80~
        return [
81~
            'acute e' => ['é', 'e'],
82~
            'grave e' => ['è', 'e'],
83~
            'circumflex a' => ['â', 'a'],
84~
            'umlaut o' => ['ö', 'o'],
85~
            'tilde n' => ['ñ', 'n'],
86~
            'cedilla c' => ['ç', 'c'],
87~
        ];
88~
    }
89~

90~
    #[Test]
91~
    public function itDetectsValidUtf8WithMultibyteChars(): void
92~
    {
93~
        $result = DiacriticsHelper::diacritics_seems_utf8('日本語');
94~
        
95~
        $this->assertTrue($result);
96~
    }
97~

98~
    #[Test]
99~
    public function itDetectsValidUtf8WithEmoji(): void
100~
    {
101~
        $result = DiacriticsHelper::diacritics_seems_utf8('Hello 👋 World');
102~
        
103~
        $this->assertTrue($result);
104~
    }
105~

106~
    #[Test]
107~
    public function itHandlesMixedCaseWithAccents(): void
108~
    {
109~
        $input = 'ÀÁÂÃÄÅ àáâãäå';
110~
        
111~
        $result = DiacriticsHelper::diacritics_remove_accents($input);
112~
        
113~
        $this->assertStringContainsString('A', $result);
114~
        $this->assertStringContainsString('a', $result);
115~
    }
116~

117~
    #[Test]
118~
    public function itDetectsEmptyStringAsUtf8(): void
119~
    {
120~
        $result = DiacriticsHelper::diacritics_seems_utf8('');
121~
        
122~
        $this->assertTrue($result);
123~
    }
124~

125~
    #[Test]
126~
    public function itHandlesNumericStrings(): void
127~
    {
128~
        $result = DiacriticsHelper::diacritics_seems_utf8('12345');
129~
        
130~
        $this->assertTrue($result);
131~
    }
132~

133~
    #[Test]
134~
    public function itRemovesAccentsFromEuropeanNames(): void
135~
    {
136~
        $input = 'François Müller';
137~
        
138~
        $result = DiacriticsHelper::diacritics_remove_accents($input);
139~
        
140~
        $this->assertStringContainsString('Francois', $result);
141~
        $this->assertStringContainsString('Muller', $result);
142~
    }
143~
}