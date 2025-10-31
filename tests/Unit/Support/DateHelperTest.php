1~
<?php
2~

3~
namespace Tests\Unit\Support;
4~

5~
use Illuminate\Support\Facades\DB;
6~
use Modules\Core\Models\Setting;
7~
use Modules\Core\Support\DateHelper;
8~
use PHPUnit\Framework\Attributes\CoversClass;
9~
use PHPUnit\Framework\Attributes\DataProvider;
10~
use PHPUnit\Framework\Attributes\Test;
11~
use Tests\Unit\UnitTestCase;
12~

13~
#[CoversClass(DateHelper::class)]
14~
class DateHelperTest extends UnitTestCase
15~
{
16~
    protected function setUp(): void
17~
    {
18~
        parent::setUp();
19~
        
20~
        DB::table('ip_settings')->delete();
21~
        Setting::setValue('default_date_format', 'm/d/Y');
22~
    }
23~

24~
    #[Test]
25~
    public function itReturnsAllDateFormats(): void
26~
    {
27~
        $formats = DateHelper::dateFormats();
28~
        
29~
        $this->assertIsArray($formats);
30~
        $this->assertArrayHasKey('d/m/Y', $formats);
31~
        $this->assertArrayHasKey('m/d/Y', $formats);
32~
        $this->assertArrayHasKey('Y-m-d', $formats);
33~
        
34~
        foreach ($formats as $format) {
35~
            $this->assertArrayHasKey('setting', $format);
36~
            $this->assertArrayHasKey('datepicker', $format);
37~
        }
38~
    }
39~

40~
    #[Test]
41~
    public function itConvertsMysqlDateToUserFormat(): void
42~
    {
43~
        Setting::setValue('default_date_format', 'm/d/Y');
44~
        
45~
        $result = DateHelper::dateFromMysql('2024-01-15');
46~
        
47~
        $this->assertSame('01/15/2024', $result);
48~
    }
49~

50~
    #[Test]
51~
    public function itConvertsMysqlDateWithEuropeanFormat(): void
52~
    {
53~
        Setting::setValue('default_date_format', 'd/m/Y');
54~
        
55~
        $result = DateHelper::dateFromMysql('2024-01-15');
56~
        
57~
        $this->assertSame('15/01/2024', $result);
58~
    }
59~

60~
    #[Test]
61~
    public function itConvertsMysqlDateWithIsoFormat(): void
62~
    {
63~
        Setting::setValue('default_date_format', 'Y-m-d');
64~
        
65~
        $result = DateHelper::dateFromMysql('2024-01-15');
66~
        
67~
        $this->assertSame('2024-01-15', $result);
68~
    }
69~

70~
    #[Test]
71~
    public function itReturnsEmptyStringForNullDate(): void
72~
    {
73~
        $result = DateHelper::dateFromMysql(null);
74~
        
75~
        $this->assertSame('', $result);
76~
    }
77~

78~
    #[Test]
79~
    public function itReturnsEmptyStringForInvalidDate(): void
80~
    {
81~
        $result = DateHelper::dateFromMysql('invalid-date');
82~
        
83~
        $this->assertSame('', $result);
84~
    }
85~

86~
    #[Test]
87~
    public function itConvertsTimestampToDate(): void
88~
    {
89~
        Setting::setValue('default_date_format', 'm/d/Y');
90~
        
91~
        $timestamp = strtotime('2024-01-15');
92~
        $result = DateHelper::dateFromTimestamp($timestamp);
93~
        
94~
        $this->assertSame('01/15/2024', $result);
95~
    }
96~

97~
    #[Test]
98~
    public function itConvertsUserDateToMysqlFormat(): void
99~
    {
100~
        Setting::setValue('default_date_format', 'm/d/Y');
101~
        
102~
        $result = DateHelper::dateToMysql('01/15/2024');
103~
        
104~
        $this->assertSame('2024-01-15', $result);
105~
    }
106~

107~
    #[Test]
108~
    public function itConvertsEuropeanDateToMysql(): void
109~
    {
110~
        Setting::setValue('default_date_format', 'd/m/Y');
111~
        
112~
        $result = DateHelper::dateToMysql('15/01/2024');
113~
        
114~
        $this->assertSame('2024-01-15', $result);
115~
    }
116~

117~
    #[Test]
118~
    public function itReturnsEmptyStringForInvalidUserDate(): void
119~
    {
120~
        Setting::setValue('default_date_format', 'm/d/Y');
121~
        
122~
        $result = DateHelper::dateToMysql('invalid');
123~
        
124~
        $this->assertSame('', $result);
125~
    }
126~

127~
    #[Test]
128~
    #[DataProvider('validDateProvider')]
129~
    public function itValidatesDates(string $date, bool $expected): void
130~
    {
131~
        Setting::setValue('default_date_format', 'm/d/Y');
132~
        
133~
        $result = DateHelper::isDate($date);
134~
        
135~
        $this->assertSame($expected, $result);
136~
    }
137~

138~
    public static function validDateProvider(): array
139~
    {
140~
        return [
141~
            'valid date' => ['01/15/2024', true],
142~
            'invalid format' => ['15-01-2024', false],
143~
            'invalid date' => ['13/32/2024', false],
144~
            'empty string' => ['', false],
145~
            'random string' => ['not a date', false],
146~
        ];
147~
    }
148~

149~
    #[Test]
150~
    public function itGetsDateFormatSetting(): void
151~
    {
152~
        Setting::setValue('default_date_format', 'Y-m-d');
153~
        
154~
        $result = DateHelper::dateFormatSetting();
155~
        
156~
        $this->assertSame('Y-m-d', $result);
157~
    }
158~

159~
    #[Test]
160~
    public function itGetsDatepickerFormat(): void
161~
    {
162~
        Setting::setValue('default_date_format', 'm/d/Y');
163~
        
164~
        $result = DateHelper::dateFormatDatepicker();
165~
        
166~
        $this->assertSame('mm/dd/yyyy', $result);
167~
    }
168~

169~
    #[Test]
170~
    public function itGetsDatepickerFormatForEuropean(): void
171~
    {
172~
        Setting::setValue('default_date_format', 'd.m.Y');
173~
        
174~
        $result = DateHelper::dateFormatDatepicker();
175~
        
176~
        $this->assertSame('dd.mm.yyyy', $result);
177~
    }
178~

179~
    #[Test]
180~
    public function itIncrementsUserDateByDays(): void
181~
    {
182~
        Setting::setValue('default_date_format', 'm/d/Y');
183~
        
184~
        $result = DateHelper::incrementUserDate('01/15/2024', '+7 days');
185~
        
186~
        $this->assertSame('01/22/2024', $result);
187~
    }
188~

189~
    #[Test]
190~
    public function itIncrementsUserDateByMonths(): void
191~
    {
192~
        Setting::setValue('default_date_format', 'm/d/Y');
193~
        
194~
        $result = DateHelper::incrementUserDate('01/15/2024', '+1 month');
195~
        
196~
        $this->assertSame('02/15/2024', $result);
197~
    }
198~

199~
    #[Test]
200~
    public function itDecrementsUserDate(): void
201~
    {
202~
        Setting::setValue('default_date_format', 'm/d/Y');
203~
        
204~
        $result = DateHelper::incrementUserDate('01/15/2024', '-7 days');
205~
        
206~
        $this->assertSame('01/08/2024', $result);
207~
    }
208~

209~
    #[Test]
210~
    public function itIncrementsMysqlDateByDays(): void
211~
    {
212~
        $result = DateHelper::incrementDate('2024-01-15', '+7 days');
213~
        
214~
        $this->assertSame('2024-01-22', $result);
215~
    }
216~

217~
    #[Test]
218~
    public function itIncrementsMysqlDateByYears(): void
219~
    {
220~
        $result = DateHelper::incrementDate('2024-01-15', '+1 year');
221~
        
222~
        $this->assertSame('2025-01-15', $result);
223~
    }
224~

225~
    #[Test]
226~
    public function itHandlesLeapYearIncrements(): void
227~
    {
228~
        $result = DateHelper::incrementDate('2024-02-29', '+1 year');
229~
        
230~
        // PHP DateTime handles this as February 28, 2025
231~
        $this->assertSame('2025-02-28', $result);
232~
    }
233~

234~
    #[Test]
235~
    public function itHandlesMonthEndIncrements(): void
236~
    {
237~
        $result = DateHelper::incrementDate('2024-01-31', '+1 month');
238~
        
239~
        // PHP DateTime handles this as February 29, 2024 (leap year)
240~
        $this->assertSame('2024-02-29', $result);
241~
    }
242~
}