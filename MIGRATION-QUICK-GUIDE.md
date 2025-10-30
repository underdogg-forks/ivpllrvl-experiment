# Quick Reference Guide for Completing Module Migration

## Current Status

**Completed:**
- ✅ All infrastructure (helpers, config, views, directory structure)
- ✅ 2 complete example modules (Family & Unit in Products)
- ✅ 42 model skeletons with PSR-4 namespaces
- ✅ 44 controller skeletons with PSR-4 namespaces

**Remaining:**
- ⚠️ 40 models need logic implementation
- ⚠️ 42 controllers need logic implementation

## How to Complete a Model

### Step-by-Step Process

1. **Open both files:**
   - Original: `application/modules/[module]/models/Mdl_[name].php`
   - Skeleton: `Modules/[TargetModule]/Entities/[Name].php`

2. **Add fillable fields:**
   ```php
   protected $fillable = [
       // Copy field names from validation_rules() in original model
       'field_name',
       'another_field',
   ];
   ```

3. **Add casts:**
   ```php
   protected $casts = [
       'id_field' => 'integer',
       'price_field' => 'decimal:2',
       'is_active' => 'boolean',
       'created_at' => 'datetime',
   ];
   ```

4. **Convert scopes:**
   ```php
   // Original CodeIgniter:
   public function is_active() {
       $this->db->where('active', 1);
   }
   
   // Laravel Eloquent:
   public function scopeActive($query) {
       return $query->where('active', 1);
   }
   ```

5. **Add relationships:**
   ```php
   // belongsTo (many-to-one)
   public function client() {
       return $this->belongsTo('Modules\Crm\Entities\Client', 'client_id', 'client_id');
   }
   
   // hasMany (one-to-many)
   public function items() {
       return $this->hasMany('Modules\Invoices\Entities\Item', 'invoice_id', 'invoice_id');
   }
   
   // hasOne
   public function amount() {
       return $this->hasOne('Modules\Invoices\Entities\InvoiceAmount', 'invoice_id');
   }
   ```

6. **Convert custom methods:**
   - Remove database queries (`$this->db->`)
   - Use Eloquent query builder
   - Make static methods if they don't need instance

### Example Reference

See `Modules/Products/Entities/Family.php` or `Modules/Products/Entities/Unit.php` for complete examples.

## How to Complete a Controller

### Step-by-Step Process

1. **Open both files:**
   - Original: `application/modules/[module]/controllers/[Name].php`
   - Skeleton: `Modules/[TargetModule]/Http/Controllers/[Name]Controller.php`

2. **Import the model:**
   ```php
   use Modules\[Module]\Entities\[ModelName];
   ```

3. **Convert index() method:**
   ```php
   // Original CodeIgniter:
   public function index($page = 0) {
       $this->mdl_model->paginate(site_url('path'), $page);
       $records = $this->mdl_model->result();
       $this->layout->set('records', $records);
       $this->layout->buffer('content', 'view');
       $this->layout->render();
   }
   
   // Laravel:
   public function index($page = 0) {
       $records = ModelName::paginate(15);
       return view('module::index', compact('records'));
   }
   ```

4. **Convert form() method:**
   ```php
   // Handle cancel
   if (request()->has('btn_cancel')) {
       return redirect()->to('path');
   }
   
   // Handle submit
   if (request()->has('btn_submit')) {
       $validated = request()->validate([
           'field_name' => 'required|string|max:255',
       ]);
       
       if ($id) {
           $model = ModelName::findOrFail($id);
           $model->update($validated);
       } else {
           ModelName::create($validated);
       }
       
       return redirect()->to('path');
   }
   
   // Load for editing
   $model = $id ? ModelName::findOrFail($id) : null;
   return view('module::form', compact('model'));
   ```

5. **Convert delete() method:**
   ```php
   public function delete($id) {
       $model = ModelName::findOrFail($id);
       $model->delete();
       return redirect()->to('path');
   }
   ```

6. **Convert other methods:**
   - Replace `$this->input->post('field')` with `request()->input('field')`
   - Replace `$this->session->set_flashdata()` with `session()->flash()`
   - Replace `redirect('path')` with `return redirect()->to('path')`
   - Replace database queries with Eloquent

### Example Reference

See `Modules/Products/Http/Controllers/FamiliesController.php` or `UnitsController.php` for complete examples.

## CodeIgniter to Laravel Pattern Conversions

| CodeIgniter | Laravel |
|------------|---------|
| `$this->load->model('mdl_name')` | `use Modules\...\Entities\Name;` |
| `$this->mdl_name->get()->result()` | `Name::all()` |
| `$this->mdl_name->where('id', $id)->get()->row()` | `Name::where('id', $id)->first()` |
| `$this->input->post('field')` | `request()->input('field')` |
| `$this->input->post()` | `request()->all()` |
| `$this->session->set_flashdata('msg', 'text')` | `session()->flash('msg', 'text')` |
| `$this->session->userdata('key')` | `session('key')` |
| `redirect('path')` | `return redirect()->to('path')` |
| `$this->db->where('field', $value)` | `Model::where('field', $value)` |
| `$this->db->insert('table', $data)` | `Model::create($data)` |
| `$this->db->update('table', $data)` | `$model->update($data)` |
| `$this->db->delete('table')` | `$model->delete()` |
| `$this->layout->render()` | `return view('module::view')` |
| `$this->layout->set('var', $val)` | Pass to `view('view', compact('var'))` |

## Common Eloquent Patterns

### Finding Records
```php
// Find by ID or fail
$model = Model::findOrFail($id);

// Find by ID or return null
$model = Model::find($id);

// First matching record
$model = Model::where('field', 'value')->first();

// All records
$models = Model::all();

// Paginated
$models = Model::paginate(15);

// With relationships
$models = Model::with('relation')->get();
```

### Creating Records
```php
// Create and save
$model = Model::create([
    'field' => 'value',
]);

// Create instance, set fields, save
$model = new Model();
$model->field = 'value';
$model->save();
```

### Updating Records
```php
// Find and update
$model = Model::findOrFail($id);
$model->update(['field' => 'value']);

// Or
$model->field = 'value';
$model->save();
```

### Deleting Records
```php
// Find and delete
$model = Model::findOrFail($id);
$model->delete();

// Delete by ID
Model::destroy($id);

// Delete multiple
Model::destroy([1, 2, 3]);
```

### Querying
```php
// Where clauses
Model::where('field', 'value')->get();
Model::where('field', '>', 10)->get();
Model::whereIn('field', [1, 2, 3])->get();
Model::whereNull('field')->get();

// Ordering
Model::orderBy('field', 'desc')->get();

// Limiting
Model::take(10)->get();
Model::limit(10)->get();

// Multiple conditions
Model::where('field1', 'value')
    ->where('field2', '>', 10)
    ->orderBy('created_at', 'desc')
    ->get();
```

## Priority Order for Migration

Start with simpler modules and work up to complex ones:

1. **Simple CRUD modules** (easiest):
   - ✅ Families (done)
   - ✅ Units (done)
   - Tax_rates
   - Payment_methods
   - Invoice_groups

2. **Moderate complexity**:
   - Products
   - Projects
   - Tasks
   - User_clients
   - Custom_fields
   - Custom_values

3. **Complex modules** (tackle last):
   - Clients
   - Invoices
   - Quotes
   - Payments
   - Users
   - Guest (7 controllers)
   - Settings (multiple controllers)
   - Core utilities (Ajax, Layout, Mailer, Upload, etc.)

## Testing After Migration

For each completed module:

1. Check syntax: `php -l Modules/[Module]/Entities/[File].php`
2. Test model can be loaded: Create a simple test script
3. Test controller methods work (may need routes first)
4. Verify views still render correctly

## Notes

- Keep views as PHP files (not Blade)
- Maintain CodeIgniter helper functions (they're in Modules/Core/Helpers)
- Database table names and structure remain unchanged (prefixed with `ip_`)
- Primary keys use original column names (not `id`)
- No timestamps on most tables (`public $timestamps = false`)

## Getting Help

- Laravel Eloquent docs: https://laravel.com/docs/10.x/eloquent
- Check existing completed examples in Modules/Products/
- Refer to MIGRATION-STATUS.md for full checklist
- Original CodeIgniter files remain in application/modules/ for reference
