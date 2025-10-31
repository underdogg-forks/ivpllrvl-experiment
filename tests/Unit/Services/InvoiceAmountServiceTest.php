1~
<?php
2~

3~
namespace Tests\Unit\Services;
4~

5~
use Illuminate\Support\Facades\DB;
6~
use Modules\Core\Models\Setting;
7~
use Modules\Invoices\Models\Invoice;
8~
use Modules\Invoices\Models\InvoiceAmount;
9~
use Modules\Invoices\Models\Item;
10~
use Modules\Invoices\Models\ItemAmount;
11~
use Modules\Invoices\Services\InvoiceAmountService;
12~
use PHPUnit\Framework\Attributes\CoversClass;
13~
use PHPUnit\Framework\Attributes\Test;
14~
use PHPUnit\Framework\TestCase;
15~

16~
#[CoversClass(InvoiceAmountService::class)]
17~
class InvoiceAmountServiceTest extends TestCase
18~
{
19~
    protected function setUp(): void
20~
    {
21~
        parent::setUp();
22~

23~
        DB::table('ip_invoice_amounts')->delete();
24~
        DB::table('ip_invoice_item_amounts')->delete();
25~
        DB::table('ip_invoice_items')->delete();
26~
        DB::table('ip_payments')->delete();
27~
        DB::table('ip_invoices')->delete();
28~

29~
        Setting::setValue('tax_rate_decimal_places', '2');
30~
        Setting::setValue('legacy_calculation', '0');
31~
    }
32~

33~
    #[Test]
34~
    public function itCalculatesInvoiceTotalsWithPayments(): void
35~
    {
36~
        $invoice = Invoice::query()->create([
37~
            'client_id'               => 1,
38~
            'user_id'                 => 1,
39~
            'invoice_group_id'        => 1,
40~
            'invoice_status_id'       => 1,
41~
            'invoice_number'          => 'INV-1000',
42~
            'invoice_date_created'    => '2024-01-01',
43~
            'invoice_date_modified'   => '2024-01-01',
44~
            'invoice_date_due'        => '2024-01-15',
45~
            'invoice_password'        => '',
46~
            'invoice_discount_amount' => 0,
47~
            'invoice_discount_percent'=> 0,
48~
            'invoice_terms'           => '',
49~
            'invoice_url_key'         => 'key-1000',
50~
        ]);
51~

52~
        $firstItem = Item::query()->create([
53~
            'invoice_id'           => $invoice->invoice_id,
54~
            'item_tax_rate_id'     => null,
55~
            'item_product_id'      => null,
56~
            'item_name'            => 'Consulting',
57~
            'item_description'     => 'Consulting hours',
58~
            'item_quantity'        => 2,
59~
            'item_price'           => 100,
60~
            'item_order'           => 1,
61~
            'item_discount_amount' => 0,
62~
            'item_product_unit'    => null,
63~
            'item_product_unit_id' => null,
64~
        ]);
65~

66~
        $secondItem = Item::query()->create([
67~
            'invoice_id'           => $invoice->invoice_id,
68~
            'item_tax_rate_id'     => null,
69~
            'item_product_id'      => null,
70~
            'item_name'            => 'Support',
71~
            'item_description'     => 'Support plan',
72~
            'item_quantity'        => 1,
73~
            'item_price'           => 150,
74~
            'item_order'           => 2,
75~
            'item_discount_amount' => 0,
76~
            'item_product_unit'    => null,
77~
            'item_product_unit_id' => null,
78~
        ]);
79~

80~
        ItemAmount::query()->create([
81~
            'item_id'        => $firstItem->item_id,
82~
            'item_subtotal'  => 200,
83~
            'item_tax_total' => 20,
84~
            'item_discount'  => 0,
85~
            'item_total'     => 220,
86~
        ]);
87~

88~
        ItemAmount::query()->create([
89~
            'item_id'        => $secondItem->item_id,
90~
            'item_subtotal'  => 150,
91~
            'item_tax_total' => 15,
92~
            'item_discount'  => 0,
93~
            'item_total'     => 165,
94~
        ]);
95~

96~
        DB::table('ip_payments')->insert([
97~
            'invoice_id'     => $invoice->invoice_id,
98~
            'payment_amount' => 100,
99~
            'payment_method' => 1,
100~
            'payment_date'   => '2024-01-10',
101~
        ]);
102~

103~
        $service = app(InvoiceAmountService::class);
104~
        $service->calculate($invoice->invoice_id);
105~

106~
        $amount = InvoiceAmount::query()->where('invoice_id', $invoice->invoice_id)->firstOrFail();
107~

108~
        $this->assertEquals(350.0, (float) $amount->invoice_item_subtotal);
109~
        $this->assertEquals(35.0, (float) $amount->invoice_item_tax_total);
110~
        $this->assertEquals(385.0, (float) $amount->invoice_total);
111~
        $this->assertEquals(100.0, (float) $amount->invoice_paid);
112~
        $this->assertEquals(285.0, (float) $amount->invoice_balance);
113~
        $this->assertEquals(0.0, (float) $amount->invoice_tax_total);
114~
    }
115~

116~
    #[Test]
117~
    public function itCalculatesInvoiceTotalsWithoutPayments(): void
118~
    {
119~
        $invoice = Invoice::query()->create([
120~
            'client_id'                => 1,
121~
            'user_id'                  => 1,
122~
            'invoice_group_id'         => 1,
123~
            'invoice_status_id'        => 1,
124~
            'invoice_number'           => 'INV-1001',
125~
            'invoice_date_created'     => '2024-01-01',
126~
            'invoice_date_modified'    => '2024-01-01',
127~
            'invoice_date_due'         => '2024-01-15',
128~
            'invoice_password'         => '',
129~
            'invoice_discount_amount'  => 0,
130~
            'invoice_discount_percent' => 0,
131~
            'invoice_terms'            => '',
132~
            'invoice_url_key'          => 'key-1001',
133~
        ]);
134~

135~
        $item = Item::query()->create([
136~
            'invoice_id'           => $invoice->invoice_id,
137~
            'item_tax_rate_id'     => null,
138~
            'item_product_id'      => null,
139~
            'item_name'            => 'Service',
140~
            'item_description'     => 'Service description',
141~
            'item_quantity'        => 1,
142~
            'item_price'           => 500,
143~
            'item_order'           => 1,
144~
            'item_discount_amount' => 0,
145~
            'item_product_unit'    => null,
146~
            'item_product_unit_id' => null,
147~
        ]);
148~

149~
        ItemAmount::query()->create([
150~
            'item_id'        => $item->item_id,
151~
            'item_subtotal'  => 500,
152~
            'item_tax_total' => 50,
153~
            'item_discount'  => 0,
154~
            'item_total'     => 550,
155~
        ]);
156~

157~
        $service = app(InvoiceAmountService::class);
158~
        $service->calculate($invoice->invoice_id);
159~

160~
        $amount = InvoiceAmount::query()->where('invoice_id', $invoice->invoice_id)->firstOrFail();
161~

162~
        $this->assertEquals(500.0, (float) $amount->invoice_item_subtotal);
163~
        $this->assertEquals(50.0, (float) $amount->invoice_item_tax_total);
164~
        $this->assertEquals(550.0, (float) $amount->invoice_total);
165~
        $this->assertEquals(0.0, (float) $amount->invoice_paid);
166~
        $this->assertEquals(550.0, (float) $amount->invoice_balance);
167~
    }
168~

169~
    #[Test]
170~
    public function itCalculatesInvoiceWithGlobalDiscount(): void
171~
    {
172~
        Setting::setValue('legacy_calculation', '0');
173~

174~
        $invoice = Invoice::query()->create([
175~
            'client_id'                => 1,
176~
            'user_id'                  => 1,
177~
            'invoice_group_id'         => 1,
178~
            'invoice_status_id'        => 1,
179~
            'invoice_number'           => 'INV-1002',
180~
            'invoice_date_created'     => '2024-01-01',
181~
            'invoice_date_modified'    => '2024-01-01',
182~
            'invoice_date_due'         => '2024-01-15',
183~
            'invoice_password'         => '',
184~
            'invoice_discount_amount'  => 0,
185~
            'invoice_discount_percent' => 0,
186~
            'invoice_terms'            => '',
187~
            'invoice_url_key'          => 'key-1002',
188~
        ]);
189~

190~
        $item = Item::query()->create([
191~
            'invoice_id'           => $invoice->invoice_id,
192~
            'item_tax_rate_id'     => null,
193~
            'item_product_id'      => null,
194~
            'item_name'            => 'Product',
195~
            'item_description'     => 'Product description',
196~
            'item_quantity'        => 1,
197~
            'item_price'           => 1000,
198~
            'item_order'           => 1,
199~
            'item_discount_amount' => 0,
200~
            'item_product_unit'    => null,
201~
            'item_product_unit_id' => null,
202~
        ]);
203~

204~
        ItemAmount::query()->create([
205~
            'item_id'        => $item->item_id,
206~
            'item_subtotal'  => 1000,
207~
            'item_tax_total' => 100,
208~
            'item_discount'  => 0,
209~
            'item_total'     => 1100,
210~
        ]);
211~

212~
        $globalDiscount = ['item' => 100.0];
213~
        $service        = app(InvoiceAmountService::class);
214~
        $service->calculate($invoice->invoice_id, $globalDiscount);
215~

216~
        $amount = InvoiceAmount::query()->where('invoice_id', $invoice->invoice_id)->firstOrFail();
217~

218~
        $this->assertEquals(900.0, (float) $amount->invoice_item_subtotal);
219~
        $this->assertEquals(1000.0, (float) $amount->invoice_total);
220~
    }
221~

222~
    #[Test]
223~
    public function itCalculatesDiscountWithAmountAndPercent(): void
224~
    {
225~
        $invoice = Invoice::query()->create([
226~
            'client_id'                => 1,
227~
            'user_id'                  => 1,
228~
            'invoice_group_id'         => 1,
229~
            'invoice_status_id'        => 1,
230~
            'invoice_number'           => 'INV-1003',
231~
            'invoice_date_created'     => '2024-01-01',
232~
            'invoice_date_modified'    => '2024-01-01',
233~
            'invoice_date_due'         => '2024-01-15',
234~
            'invoice_password'         => '',
235~
            'invoice_discount_amount'  => 50,
236~
            'invoice_discount_percent' => 10,
237~
            'invoice_terms'            => '',
238~
            'invoice_url_key'          => 'key-1003',
239~
        ]);
240~

241~
        $service = app(InvoiceAmountService::class);
242~
        $result  = $service->calculateDiscount($invoice->invoice_id, 1000, 2);
243~

244~
        // 1000 - 50 = 950, then 950 - (950 * 10 / 100) = 855
245~
        $this->assertEquals(855.0, $result);
246~
    }
247~

248~
    #[Test]
249~
    public function itReturnsZeroForGlobalDiscountWhenNoItems(): void
250~
    {
251~
        $invoice = Invoice::query()->create([
252~
            'client_id'                => 1,
253~
            'user_id'                  => 1,
254~
            'invoice_group_id'         => 1,
255~
            'invoice_status_id'        => 1,
256~
            'invoice_number'           => 'INV-1004',
257~
            'invoice_date_created'     => '2024-01-01',
258~
            'invoice_date_modified'    => '2024-01-01',
259~
            'invoice_date_due'         => '2024-01-15',
260~
            'invoice_password'         => '',
261~
            'invoice_discount_amount'  => 0,
262~
            'invoice_discount_percent' => 0,
263~
            'invoice_terms'            => '',
264~
            'invoice_url_key'          => 'key-1004',
265~
        ]);
266~

267~
        $service = app(InvoiceAmountService::class);
268~
        $result  = $service->getGlobalDiscount($invoice->invoice_id);
269~

270~
        $this->assertEquals(0.0, $result);
271~
    }
272~

273~
    #[Test]
274~
    public function itGetsTotalInvoicedForMonth(): void
275~
    {
276~
        $service = app(InvoiceAmountService::class);
277~
        $result  = $service->getTotalInvoiced('month');
278~

279~
        $this->assertIsFloat($result);
280~
        $this->assertGreaterThanOrEqual(0.0, $result);
281~
    }
282~

283~
    #[Test]
284~
    public function itGetsTotalPaidForYear(): void
285~
    {
286~
        $service = app(InvoiceAmountService::class);
287~
        $result  = $service->getTotalPaid('year');
288~

289~
        $this->assertIsFloat($result);
290~
        $this->assertGreaterThanOrEqual(0.0, $result);
291~
    }
292~

293~
    #[Test]
294~
    public function itGetsTotalBalanceForLastMonth(): void
295~
    {
296~
        $service = app(InvoiceAmountService::class);
297~
        $result  = $service->getTotalBalance('last_month');
298~

299~
        $this->assertIsFloat($result);
300~
        $this->assertGreaterThanOrEqual(0.0, $result);
301~
    }
302~

303~
    #[Test]
304~
    public function itGetsStatusTotalsForThisMonth(): void
305~
    {
306~
        $service = app(InvoiceAmountService::class);
307~
        $result  = $service->getStatusTotals('this-month');
308~

309~
        $this->assertIsArray($result);
310~
        $this->assertArrayHasKey(1, $result); // Draft
311~
        $this->assertArrayHasKey(2, $result); // Sent
312~
        $this->assertArrayHasKey(3, $result); // Viewed
313~
        $this->assertArrayHasKey(4, $result); // Paid
314~

315~
        foreach ($result as $status) {
316~
            $this->assertArrayHasKey('invoice_status_id', $status);
317~
            $this->assertArrayHasKey('sum_total', $status);
318~
            $this->assertArrayHasKey('sum_paid', $status);
319~
            $this->assertArrayHasKey('sum_balance', $status);
320~
            $this->assertArrayHasKey('num_total', $status);
321~
        }
322~
    }
323~

324~
    #[Test]
325~
    public function itGetsStatusTotalsForDifferentPeriods(): void
326~
    {
327~
        $service = app(InvoiceAmountService::class);
328~

329~
        $periods = ['last-month', 'this-quarter', 'last-quarter', 'this-year', 'last-year'];
330~

331~
        foreach ($periods as $period) {
332~
            $result = $service->getStatusTotals($period);
333~
            $this->assertIsArray($result);
334~
            $this->assertCount(4, $result);
335~
        }
336~
    }
337~
}