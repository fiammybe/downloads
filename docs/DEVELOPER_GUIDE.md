# Developer Guide - Downloads Module 2.0

This guide provides comprehensive documentation for developers working with or extending the Downloads module.

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Controller Layer](#controller-layer)
3. [Model Layer (Handlers & Entities)](#model-layer)
4. [View Layer (Templates)](#view-layer)
5. [Fluent Query Builder](#fluent-query-builder)
6. [Security](#security)
7. [Extending the Module](#extending-the-module)
8. [API Reference](#api-reference)
9. [Best Practices](#best-practices)
10. [Testing](#testing)

---

## Architecture Overview

Downloads 2.0 follows a modern MVC (Model-View-Controller) architecture:

```
┌─────────────────────────────────────────────────────────┐
│                    Entry Points                          │
│  (index.php, category.php, download.php, etc.)          │
│  - Minimal routing logic                                 │
│  - Load and instantiate controllers                      │
└────────────────────┬────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────┐
│                   Controllers                            │
│  (IndexController, CategoryController, etc.)             │
│  - Handle HTTP requests                                  │
│  - Validate input and permissions                        │
│  - Coordinate between models and views                   │
│  - Render templates                                      │
└────────────┬───────────────────────────┬────────────────┘
             │                           │
             ↓                           ↓
┌─────────────────────────┐   ┌──────────────────────────┐
│    Model Layer          │   │     View Layer           │
│  - Handlers (Data)      │   │  - Smarty Templates      │
│  - Entities (Objects)   │   │  - Presentation Logic    │
│  - Business Logic       │   │  - User Interface        │
└─────────────────────────┘   └──────────────────────────┘
```

### Key Principles

- **Separation of Concerns** - Each layer has a specific responsibility
- **Type Safety** - Full type hints throughout (PHP 8.2+)
- **Security First** - CSRF protection, input validation, output escaping
- **Fluent Interface** - Chainable query builder for database operations
- **DRY (Don't Repeat Yourself)** - Common functionality in base classes

---

## Controller Layer

### BaseController

All controllers extend `BaseController`, which provides common functionality:

```php
class BaseController
{
    protected DownloadsCategoryHandler $categoryHandler;
    protected DownloadsDownloadHandler $downloadHandler;
    
    // Input methods
    protected function getInt(string $key, int $default = 0): int
    protected function getString(string $key, string $default = ''): string
    protected function postInt(string $key, int $default = 0): int
    protected function postString(string $key, string $default = ''): string
    
    // Security
    protected function validateToken(): bool
    protected function checkPermission(string $perm, int $itemId = 0): bool
    protected function isAdmin(): bool
    
    // Rendering
    protected function render(string $template, array $data = []): void
    protected function redirect(string $url, int $time = 3, string $message = ''): void
    
    // Logging
    protected function logAction(string $case, int $itemId, string $item): void
}
```

### Creating a Custom Controller

```php
<?php

namespace Downloads\Controllers;

class CustomController extends BaseController
{
    /**
     * Handle the request
     */
    public function handle(): void
    {
        $op = $this->getString('op', 'list');
        
        match ($op) {
            'list' => $this->listItems(),
            'view' => $this->viewItem(),
            'edit' => $this->editItem(),
            'save' => $this->saveItem(),
            default => $this->listItems()
        };
    }
    
    /**
     * List all items
     */
    protected function listItems(): void
    {
        // Check permissions
        if (!$this->checkPermission('view')) {
            $this->redirect('index.php', 3, 'Access denied');
            return;
        }
        
        // Get data using fluent interface
        $items = $this->downloadHandler
            ->where('download_active', 1)
            ->where('download_approve', 1)
            ->orderBy('download_title')
            ->limit(20)
            ->get();
        
        // Render template
        $this->render('downloads_custom_list.html', [
            'items' => $items,
            'page_title' => 'Custom List'
        ]);
    }
    
    /**
     * Save an item
     */
    protected function saveItem(): void
    {
        // Validate CSRF token
        if (!$this->validateToken()) {
            $this->redirect('custom.php', 2, 'Invalid security token');
            return;
        }
        
        // Check permissions
        if (!$this->isAdmin()) {
            $this->redirect('index.php', 3, 'Access denied');
            return;
        }
        
        // Get and validate input
        $id = $this->postInt('download_id');
        $title = $this->postString('download_title');
        
        // Get or create object
        $downloadObj = $id > 0 
            ? $this->downloadHandler->get($id)
            : $this->downloadHandler->create();
        
        // Set values
        $downloadObj->setVar('download_title', $title);
        // ... set other fields
        
        // Save
        if ($this->downloadHandler->insert($downloadObj)) {
            $this->logAction('modified', $downloadObj->id(), 'download');
            $this->redirect('custom.php', 2, 'Saved successfully');
        } else {
            $this->redirect('custom.php', 2, 'Save failed');
        }
    }
}
```

---

## Model Layer

### Handlers (Data Access)

Handlers manage database operations and implement the fluent query builder.

#### Fluent Query Builder

```php
// Basic queries
$downloads = $downloadHandler->get($id);
$downloads = $downloadHandler->getObjects();

// Fluent interface
$downloads = $downloadHandler
    ->where('download_active', 1)
    ->where('download_cid', $categoryId)
    ->orderBy('download_published_date', 'DESC')
    ->limit(10)
    ->get();

// Single result
$download = $downloadHandler
    ->where('download_id', $id)
    ->where('download_active', 1)
    ->first();

// Count
$count = $downloadHandler
    ->where('download_active', 1)
    ->count();

// Complex conditions
$downloads = $downloadHandler
    ->where('download_active', 1)
    ->where('download_approve', 1)
    ->whereIn('download_cid', [1, 2, 3])
    ->whereLike('download_title', '%search%')
    ->orderBy('download_title')
    ->limit(20, 0)  // limit, offset
    ->get();
```

### Entities (Objects)

Entities represent database records with typed properties.

```php
class Download extends icms_ipf_seo_Object
{
    public int $download_id;
    public string $download_title;
    public int $download_cid;
    public bool $download_active;
    // ... other properties
    
    /**
     * Get the download title
     */
    public function getDownloadTitle(): string
    {
        return $this->getVar('download_title', 'e');
    }
    
    /**
     * Check if user can edit
     */
    public function userCanEditAndDelete(): bool
    {
        global $icmsUser;
        
        if (!$icmsUser) {
            return false;
        }
        
        if ($icmsUser->isAdmin($this->handler->_moduleid)) {
            return true;
        }
        
        return $this->getVar('download_submitter') == $icmsUser->uid();
    }
}
```

---

## View Layer

### Template Structure

Templates use Smarty syntax and are located in `/templates/`:

```smarty
{* downloads_custom.html *}
<div class="downloads-custom">
    <h1>{$page_title}</h1>
    
    {foreach from=$items item=download}
        <div class="download-item">
            <h2><a href="singledownload.php?download_id={$download.download_id}">
                {$download.download_title}
            </a></h2>
            <p>{$download.download_teaser}</p>
            <div class="download-meta">
                <span>Category: {$download.category_title}</span>
                <span>Downloads: {$download.counter}</span>
            </div>
        </div>
    {/foreach}
</div>
```

### Passing Data to Templates

```php
// In controller
$this->render('downloads_custom.html', [
    'page_title' => 'My Custom Page',
    'items' => $downloads,
    'total_count' => $count,
    'user_can_edit' => $this->isAdmin()
]);
```

---

## Fluent Query Builder

### Available Methods

#### where(string $field, mixed $value, string $operator = '='): self
```php
$handler->where('download_active', 1);
$handler->where('counter', 100, '>');
```

#### whereIn(string $field, array $values): self
```php
$handler->whereIn('download_cid', [1, 2, 3]);
```

#### whereLike(string $field, string $value): self
```php
$handler->whereLike('download_title', '%search%');
```

#### orderBy(string $field, string $direction = 'ASC'): self
```php
$handler->orderBy('download_title');
$handler->orderBy('download_published_date', 'DESC');
```

#### limit(int $limit, int $offset = 0): self
```php
$handler->limit(10);        // First 10 records
$handler->limit(10, 20);    // Records 21-30
```

#### get(): array
```php
$downloads = $handler->where('download_active', 1)->get();
// Returns array of Download objects
```

#### first(): ?object
```php
$download = $handler->where('download_id', $id)->first();
// Returns single Download object or null
```

#### count(): int
```php
$total = $handler->where('download_active', 1)->count();
// Returns integer count
```

### Chaining Example

```php
$recentDownloads = $downloadHandler
    ->where('download_active', 1)
    ->where('download_approve', 1)
    ->whereIn('download_cid', $allowedCategories)
    ->orderBy('download_published_date', 'DESC')
    ->limit(10)
    ->get();
```

---

## Security

### CSRF Protection

All forms must include security tokens:

```php
// In controller - validate token
if (!$this->validateToken()) {
    $this->redirect('index.php', 2, 'Invalid security token');
    return;
}

// In template - include token
{$security_token}
```

### Input Validation

Always validate and sanitize input:

```php
// Get sanitized input
$id = $this->postInt('download_id');           // Integer
$title = $this->postString('download_title');  // String
$email = $this->postString('email');           // String

// Additional validation
if (empty($title)) {
    $this->redirect('download.php', 2, 'Title is required');
    return;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $this->redirect('download.php', 2, 'Invalid email');
    return;
}
```

### Output Escaping

Always escape output in templates:

```smarty
{* Escaped by default *}
{$download.download_title}

{* Explicitly escape *}
{$download.download_title|escape}

{* For HTML content (use carefully) *}
{$download.download_description}
```

### Permission Checks

Always check permissions:

```php
// Check if user can view
if (!$this->checkPermission('downloads_grpperm', $downloadId)) {
    $this->redirect('index.php', 3, 'Access denied');
    return;
}

// Check if user is admin
if (!$this->isAdmin()) {
    $this->redirect('index.php', 3, 'Admin access required');
    return;
}
```

---

## Extending the Module

### Adding a Custom Field

1. **Add database column**:
```sql
ALTER TABLE `{prefix}_downloads_download` 
ADD COLUMN `custom_field` VARCHAR(255) DEFAULT '' AFTER `download_title`;
```

2. **Update Entity class** (`class/Download.php`):
```php
public string $custom_field = '';

public function getCustomField(): string
{
    return $this->getVar('custom_field', 'e');
}
```

3. **Update Handler** (`class/DownloadHandler.php`):
```php
protected function setupFieldDefinitions(): void
{
    parent::setupFieldDefinitions();
    
    $this->table->addColumn('custom_field', [
        'type' => 'text',
        'length' => 255,
        'default' => ''
    ]);
}
```

4. **Update form** in controller or template

5. **Update display** in template

### Creating a Custom Block

```php
<?php
/**
 * Custom Downloads Block
 */
function downloads_custom_block_show($options)
{
    $downloadHandler = icms_getModuleHandler('download', 'downloads');
    
    $downloads = $downloadHandler
        ->where('download_active', 1)
        ->where('download_approve', 1)
        ->orderBy('download_published_date', 'DESC')
        ->limit($options[0])
        ->get();
    
    $block = [];
    $block['downloads'] = $downloads;
    $block['show_counter'] = $options[1];
    
    return $block;
}

function downloads_custom_block_edit($options)
{
    $form = '<label>Number of downloads:</label>';
    $form .= '<input type="text" name="options[0]" value="' . $options[0] . '" /><br>';
    $form .= '<label>Show counter:</label>';
    $form .= '<input type="checkbox" name="options[1]" value="1"' . ($options[1] ? ' checked' : '') . ' />';
    
    return $form;
}
```

---

## API Reference

See inline PHPDoc comments in source files for complete API documentation.

### Key Classes

- `BaseController` - Base controller with common functionality
- `DownloadsCategoryHandler` - Category data access
- `DownloadsDownloadHandler` - Download data access  
- `DownloadsCategory` - Category entity
- `DownloadsDownload` - Download entity

### Key Methods

See source code for complete method signatures and documentation.

---

## Best Practices

1. **Always use type hints** - Parameters and return types
2. **Validate all input** - Never trust user input
3. **Check permissions** - Before any sensitive operation
4. **Use fluent interface** - For database queries
5. **Escape output** - In templates
6. **Log important actions** - Using `logAction()`
7. **Follow PSR-12** - Coding standards
8. **Write PHPDoc** - For all public methods
9. **Use match expressions** - Instead of switch
10. **Keep controllers thin** - Move complex logic to handlers

---

## Testing

### Manual Testing Checklist

- [ ] Create category
- [ ] Edit category
- [ ] Delete category
- [ ] Add download
- [ ] Edit download
- [ ] Delete download
- [ ] Download file
- [ ] Submit review
- [ ] Test permissions
- [ ] Test search
- [ ] Test blocks
- [ ] Test RSS feeds

### Unit Testing (Future)

The controller-based architecture enables unit testing:

```php
// Example unit test (PHPUnit)
class IndexControllerTest extends TestCase
{
    public function testListDownloads()
    {
        $controller = new IndexController();
        // Test logic
    }
}
```

---

## Additional Resources

- [README.md](../README.md) - Module overview
- [CHANGELOG.md](../CHANGELOG.md) - Version history
- [MIGRATION.md](../MIGRATION.md) - Upgrade guide
- [SECURITY_AUDIT.md](../SECURITY_AUDIT.md) - Security assessment
- [ImpressCMS Documentation](https://www.impresscms.org/documentation/)

---

**Happy Coding!** 🚀

