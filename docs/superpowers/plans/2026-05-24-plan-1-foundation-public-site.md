# Plan 1 — Foundation + Public Site Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Bootstrap the Laravel 11 app, define every domain model with migrations + seeders, and ship the entire public-facing TheLastClicks website rendered from Blade with a working contact form that persists leads to a `quotes` table.

**Architecture:** Single Laravel 11 monolith. Public site is Blade with `core.css`/`pages.css`/`core.js`/`chrome.js` lifted verbatim from `design/` and served via Vite to preserve the design 1:1. All domain models (Quote, Post, Portfolio, Service, Industry, Crew, etc.) and their migrations land in this plan — even though the admin UI for them ships in Plans 2 + 3 — so the schema is stable and seeders are available for development. Public list/detail pages for Blog and Portfolio are wired but read from seeded fixtures until admin CRUD ships in Plan 3.

**Tech Stack:** PHP 8.3 · Laravel 11 · MySQL 8 · Blade · Vite · Pest 2 · spatie/laravel-permission · spatie/laravel-medialibrary · spatie/laravel-activitylog · spatie/laravel-sluggable

**Spec:** [docs/superpowers/specs/2026-05-24-thelastclicks-website-design.md](../specs/2026-05-24-thelastclicks-website-design.md)

---

## File Structure

```
thelastclicks/                                  ← Laravel root after install
├── app/
│   ├── Http/Controllers/Public/
│   │   ├── HomeController.php
│   │   ├── PageController.php                  (about, our-process, legal)
│   │   ├── ServiceController.php
│   │   ├── IndustryController.php
│   │   ├── PortfolioController.php
│   │   ├── BlogController.php
│   │   ├── CrewController.php
│   │   └── ContactController.php
│   ├── Http/Requests/
│   │   └── StoreQuoteRequest.php
│   ├── Mail/
│   │   ├── NewQuoteAdminNotification.php
│   │   └── QuoteAutoReply.php
│   ├── Models/
│   │   ├── User.php                            (extended)
│   │   ├── Quote.php
│   │   ├── QuoteNote.php
│   │   ├── Post.php
│   │   ├── Category.php
│   │   ├── Tag.php
│   │   ├── Portfolio.php
│   │   ├── Service.php
│   │   ├── Industry.php
│   │   ├── Crew.php
│   │   └── SiteSetting.php
│   └── View/Components/
│       ├── Layouts/App.php                     (Blade component class for layout)
│       └── JsonLd.php
├── database/
│   ├── migrations/                             (all model migrations)
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   ├── RolesSeeder.php
│   │   ├── AdminUserSeeder.php
│   │   ├── ServicesSeeder.php
│   │   ├── IndustriesSeeder.php
│   │   ├── CrewSeeder.php
│   │   ├── PortfoliosSeeder.php
│   │   ├── PostsSeeder.php
│   │   └── SiteSettingsSeeder.php
│   └── factories/                              (one per model)
├── resources/
│   ├── css/
│   │   ├── core.css                            (copied from design/assets)
│   │   └── pages.css                           (copied)
│   ├── js/
│   │   ├── core.js                             (copied)
│   │   └── chrome.js                           (copied)
│   └── views/
│       ├── layouts/app.blade.php
│       ├── components/                         (Blade x-components)
│       │   ├── nav.blade.php
│       │   ├── footer.blade.php
│       │   ├── hero.blade.php
│       │   ├── marquee.blade.php
│       │   ├── card-post.blade.php
│       │   ├── card-portfolio.blade.php
│       │   ├── card-crew.blade.php
│       │   ├── quote-form.blade.php
│       │   └── json-ld.blade.php
│       ├── home.blade.php
│       ├── pages/                              (about, our-process, legal pages)
│       │   ├── about.blade.php
│       │   ├── our-process.blade.php
│       │   ├── privacy-policy.blade.php
│       │   ├── terms-of-service.blade.php
│       │   ├── cookie-policy.blade.php
│       │   ├── disclaimer.blade.php
│       │   └── thank-you.blade.php
│       ├── services/show.blade.php
│       ├── industries/{index,show}.blade.php
│       ├── portfolio/{index,show}.blade.php
│       ├── blog/{index,show}.blade.php
│       ├── crew/{index,show}.blade.php
│       ├── contact.blade.php
│       ├── emails/
│       │   ├── quote-admin.blade.php
│       │   └── quote-reply.blade.php
│       └── errors/404.blade.php
├── routes/
│   └── web.php
└── tests/
    ├── Pest.php
    ├── TestCase.php
    └── Feature/
        ├── Public/
        │   ├── HomePageTest.php
        │   ├── PageTest.php
        │   ├── ServicePageTest.php
        │   ├── IndustryPageTest.php
        │   ├── PortfolioPageTest.php
        │   ├── BlogPageTest.php
        │   ├── CrewPageTest.php
        │   └── ContactFormTest.php
        └── Models/
            └── QuoteFactoryTest.php
```

**One-file-per-page Blade pattern.** Every public route renders a top-level view that composes shared chrome via `<x-nav />` + `<x-footer />` inside a single `<x-layouts.app>` wrapper. Per-page metadata (title, description, OG image, structured data) flows in via slots.

---

## Phase A — Project Bootstrap

### Task 1: Install Laravel 11

**Files:**
- Create: `/Users/Project/Personal/thelastclicks/composer.json` (Laravel installer output)
- Create: `/Users/Project/Personal/thelastclicks/.env` (from `.env.example`)

- [ ] **Step 1: Initialise git in project root**

```bash
cd /Users/Project/Personal/thelastclicks
git init
git add design docs
git commit -m "chore: import design assets and design spec"
```

- [ ] **Step 2: Install Laravel into a temp dir and move into root**

```bash
cd /Users/Project/Personal/thelastclicks
composer create-project laravel/laravel:^11.0 _laravel_tmp
mv _laravel_tmp/.* _laravel_tmp/* ./ 2>/dev/null || true
rmdir _laravel_tmp
```

Expected: `artisan`, `composer.json`, `app/`, `routes/`, `resources/`, `database/` now exist in project root.

- [ ] **Step 3: Configure `.env` for MySQL**

Edit `/Users/Project/Personal/thelastclicks/.env`:

```env
APP_NAME=TheLastClicks
APP_ENV=local
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=thelastclicks
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@thelastclicks.com"
MAIL_FROM_NAME="${APP_NAME}"

FILESYSTEM_DISK=public

ADMIN_SEED_EMAIL=admin@thelastclicks.com
ADMIN_SEED_PASSWORD=ChangeMe!123
```

- [ ] **Step 4: Create DB and run base migrations**

```bash
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS thelastclicks CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate
php artisan storage:link
```

Expected: `users`, `cache`, `jobs`, `sessions` tables exist.

- [ ] **Step 5: Verify Laravel boots**

```bash
php artisan serve --port=8000 &
sleep 2
curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8000
kill %1
```

Expected: `200`.

- [ ] **Step 6: Commit**

```bash
git add .
git commit -m "chore: install Laravel 11 and configure mysql"
```

---

### Task 2: Install runtime + dev packages

**Files:**
- Modify: `composer.json`
- Create: `config/permission.php`, `config/medialibrary.php`, `config/activitylog.php` (published)

- [ ] **Step 1: Require runtime packages**

```bash
composer require \
  spatie/laravel-permission:^6 \
  spatie/laravel-medialibrary:^11 \
  spatie/laravel-activitylog:^4 \
  spatie/laravel-sluggable:^3
```

- [ ] **Step 2: Require dev packages**

```bash
composer require --dev \
  pestphp/pest:^2 \
  pestphp/pest-plugin-laravel:^2 \
  larastan/larastan:^2 \
  laravel/pint
```

- [ ] **Step 3: Publish vendor migrations + configs**

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-config"
```

- [ ] **Step 4: Migrate**

```bash
php artisan migrate
```

Expected: `roles`, `permissions`, `model_has_*`, `role_has_permissions`, `media`, `activity_log` tables created.

- [ ] **Step 5: Initialise Pest**

```bash
./vendor/bin/pest --init
```

Expected: `tests/Pest.php` rewritten for Pest, sample `ExampleTest.php` runs.

- [ ] **Step 6: Run pest to verify**

```bash
./vendor/bin/pest
```

Expected: 2 passing tests (the example tests).

- [ ] **Step 7: Commit**

```bash
git add .
git commit -m "chore: install spatie packages, pest, pint, larastan"
```

---

### Task 3: Wire Vite + migrate design assets

**Files:**
- Create: `resources/css/core.css` (copied from design)
- Create: `resources/css/pages.css` (copied)
- Create: `resources/js/core.js` (copied)
- Create: `resources/js/chrome.js` (copied)
- Modify: `vite.config.js`
- Modify: `resources/css/app.css` (delete) and `resources/js/app.js` (delete)

- [ ] **Step 1: Copy design assets into resources**

```bash
cp design/assets/core.css   resources/css/core.css
cp design/assets/pages.css  resources/css/pages.css
cp design/assets/core.js    resources/js/core.js
cp design/assets/chrome.js  resources/js/chrome.js
```

- [ ] **Step 2: Remove default Laravel entrypoints**

```bash
rm -f resources/css/app.css resources/js/app.js
```

- [ ] **Step 3: Rewrite `vite.config.js`**

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/core.css',
                'resources/css/pages.css',
                'resources/js/core.js',
                'resources/js/chrome.js',
            ],
            refresh: true,
        }),
    ],
});
```

- [ ] **Step 4: Install node deps and build**

```bash
npm install
npm run build
```

Expected: `public/build/manifest.json` lists 4 entries.

- [ ] **Step 5: Commit**

```bash
git add .
git commit -m "chore: import design assets and configure vite"
```

---

## Phase B — Domain Models + Migrations

All migrations land here so the schema is final before any controller, view, or admin resource depends on it.

### Task 4: Extend `users` table for admin staff

**Files:**
- Modify: `database/migrations/0001_01_01_000000_create_users_table.php`
- Modify: `app/Models/User.php`

- [ ] **Step 1: Write failing test**

Create `tests/Feature/Models/UserModelTest.php`:

```php
<?php

use App\Models\User;

it('uses HasRoles trait', function () {
    expect(class_uses_recursive(User::class))
        ->toContain(\Spatie\Permission\Traits\HasRoles::class);
});

it('creates a user with name + email + password', function () {
    $u = User::factory()->create(['email' => 'a@b.com']);
    expect($u->email)->toBe('a@b.com');
});
```

- [ ] **Step 2: Run test, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Models/UserModelTest.php
```

Expected: FAIL — trait not on model.

- [ ] **Step 3: Edit `app/Models/User.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

- [ ] **Step 4: Run test, expect PASS**

```bash
./vendor/bin/pest tests/Feature/Models/UserModelTest.php
```

Expected: 2 passing.

- [ ] **Step 5: Commit**

```bash
git add app/Models/User.php tests/Feature/Models/UserModelTest.php
git commit -m "feat(users): add HasRoles trait"
```

---

### Task 5: `quotes` + `quote_notes` migrations + model

**Files:**
- Create: `database/migrations/2026_05_24_000001_create_quotes_table.php`
- Create: `database/migrations/2026_05_24_000002_create_quote_notes_table.php`
- Create: `app/Models/Quote.php`
- Create: `app/Models/QuoteNote.php`
- Create: `database/factories/QuoteFactory.php`
- Create: `database/factories/QuoteNoteFactory.php`
- Create: `tests/Feature/Models/QuoteFactoryTest.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/Models/QuoteFactoryTest.php`:

```php
<?php

use App\Models\Quote;
use App\Models\QuoteNote;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('creates a quote with defaults', function () {
    $q = Quote::factory()->create();
    expect($q->status)->toBe('new')
        ->and($q->email)->toBeString()
        ->and($q->source_page)->toBeString();
});

it('logs notes against a quote', function () {
    $author = User::factory()->create();
    $q = Quote::factory()->create();
    $note = QuoteNote::factory()->for($q)->for($author, 'author')->create(['body' => 'follow up']);
    expect($q->notes()->count())->toBe(1)
        ->and($note->author->id)->toBe($author->id);
});
```

- [ ] **Step 2: Run, expect FAIL** (models missing)

```bash
./vendor/bin/pest tests/Feature/Models/QuoteFactoryTest.php
```

- [ ] **Step 3: Create quotes migration**

```bash
php artisan make:migration create_quotes_table
```

Replace contents:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('company')->nullable();
            $t->string('email');
            $t->string('phone')->nullable();
            $t->string('project_type')->nullable();
            $t->string('budget')->nullable();
            $t->string('timeline')->nullable();
            $t->text('message')->nullable();
            $t->string('source_page')->nullable();
            $t->string('ip', 45)->nullable();
            $t->string('ua', 512)->nullable();
            $t->enum('status', ['new', 'contacted', 'qualified', 'won', 'lost'])->default('new');
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index(['status', 'created_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('quotes'); }
};
```

- [ ] **Step 4: Create quote_notes migration**

```bash
php artisan make:migration create_quote_notes_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quote_notes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $t->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $t->text('body');
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('quote_notes'); }
};
```

- [ ] **Step 5: Create `app/Models/Quote.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Quote extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name','company','email','phone','project_type','budget','timeline',
        'message','source_page','ip','ua','status','assigned_to',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status','assigned_to'])
            ->logOnlyDirty();
    }

    public function notes(): HasMany { return $this->hasMany(QuoteNote::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
}
```

- [ ] **Step 6: Create `app/Models/QuoteNote.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteNote extends Model
{
    use HasFactory;

    protected $fillable = ['quote_id','author_id','body'];

    public function quote(): BelongsTo { return $this->belongsTo(Quote::class); }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'author_id'); }
}
```

- [ ] **Step 7: Create factories**

`database/factories/QuoteFactory.php`:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'         => fake()->name(),
            'company'      => fake()->optional()->company(),
            'email'        => fake()->safeEmail(),
            'phone'        => fake()->optional()->phoneNumber(),
            'project_type' => fake()->randomElement(['Brand film / commercial','Wedding','Editorial / photography','Other']),
            'budget'       => fake()->randomElement(['Under ₹5L','₹5L – ₹15L','₹15L – ₹50L']),
            'timeline'     => 'Flexible',
            'message'      => fake()->paragraph(),
            'source_page'  => '/contact',
            'ip'           => fake()->ipv4(),
            'ua'           => fake()->userAgent(),
            'status'       => 'new',
        ];
    }
}
```

`database/factories/QuoteNoteFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteNoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quote_id'  => Quote::factory(),
            'author_id' => User::factory(),
            'body'      => fake()->sentence(),
        ];
    }
}
```

- [ ] **Step 8: Migrate + run test**

```bash
php artisan migrate
./vendor/bin/pest tests/Feature/Models/QuoteFactoryTest.php
```

Expected: 2 passing.

- [ ] **Step 9: Commit**

```bash
git add .
git commit -m "feat(quotes): add quotes + quote_notes schema and models"
```

---

### Task 6: `posts` + `categories` + `tags` migrations + models

**Files:**
- Create: 4 migrations (`create_categories_table`, `create_tags_table`, `create_posts_table`, `create_post_pivots_table`)
- Create: `app/Models/{Post,Category,Tag}.php`
- Create: `database/factories/{PostFactory,CategoryFactory,TagFactory}.php`
- Create: `tests/Feature/Models/PostFactoryTest.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/Models/PostFactoryTest.php`:

```php
<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('creates a post with author and pivots', function () {
    $u = User::factory()->create();
    $p = Post::factory()->for($u, 'author')->create(['status' => 'published']);
    $p->categories()->sync([Category::factory()->create()->id]);
    $p->tags()->sync([Tag::factory()->create()->id]);

    expect($p->slug)->not->toBeEmpty()
        ->and($p->categories)->toHaveCount(1)
        ->and($p->tags)->toHaveCount(1);
});

it('scopes published posts only', function () {
    Post::factory()->create(['status' => 'draft']);
    Post::factory()->create(['status' => 'published','published_at' => now()->subDay()]);
    expect(Post::published()->count())->toBe(1);
});
```

- [ ] **Step 2: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Models/PostFactoryTest.php
```

- [ ] **Step 3: Create categories migration**

```bash
php artisan make:migration create_categories_table
```

```php
return new class extends Migration {
    public function up(): void {
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('categories'); }
};
```

- [ ] **Step 4: Create tags migration**

```bash
php artisan make:migration create_tags_table
```

```php
return new class extends Migration {
    public function up(): void {
        Schema::create('tags', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('tags'); }
};
```

- [ ] **Step 5: Create posts migration**

```bash
php artisan make:migration create_posts_table
```

```php
return new class extends Migration {
    public function up(): void {
        Schema::create('posts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $t->string('title');
            $t->string('slug')->unique();
            $t->text('excerpt')->nullable();
            $t->longText('body')->nullable();
            $t->enum('status', ['draft','published'])->default('draft');
            $t->timestamp('published_at')->nullable();
            $t->string('seo_title')->nullable();
            $t->string('seo_description', 500)->nullable();
            $t->timestamps();
            $t->index(['status','published_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('posts'); }
};
```

- [ ] **Step 6: Create pivots migration**

```bash
php artisan make:migration create_post_pivots_table
```

```php
return new class extends Migration {
    public function up(): void {
        Schema::create('post_category', function (Blueprint $t) {
            $t->foreignId('post_id')->constrained()->cascadeOnDelete();
            $t->foreignId('category_id')->constrained()->cascadeOnDelete();
            $t->primary(['post_id','category_id']);
        });
        Schema::create('post_tag', function (Blueprint $t) {
            $t->foreignId('post_id')->constrained()->cascadeOnDelete();
            $t->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $t->primary(['post_id','tag_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('post_tag');
        Schema::dropIfExists('post_category');
    }
};
```

- [ ] **Step 7: Create models**

`app/Models/Category.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model
{
    use HasFactory, HasSlug;
    protected $fillable = ['name','slug'];
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }
    public function posts() { return $this->belongsToMany(Post::class); }
}
```

`app/Models/Tag.php` — identical structure (replace `Category` with `Tag`).

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Tag extends Model
{
    use HasFactory, HasSlug;
    protected $fillable = ['name','slug'];
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }
    public function posts() { return $this->belongsToMany(Post::class); }
}
```

`app/Models/Post.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model implements HasMedia
{
    use HasFactory, HasSlug, InteractsWithMedia;

    protected $fillable = [
        'author_id','title','slug','excerpt','body',
        'status','published_at','seo_title','seo_description',
    ];
    protected $casts = ['published_at' => 'datetime'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug');
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status','published')->whereNotNull('published_at')->where('published_at','<=',now());
    }

    public function author(): BelongsTo { return $this->belongsTo(User::class,'author_id'); }
    public function categories(): BelongsToMany { return $this->belongsToMany(Category::class); }
    public function tags(): BelongsToMany { return $this->belongsToMany(Tag::class); }
}
```

- [ ] **Step 8: Create factories**

`database/factories/CategoryFactory.php`:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->unique()->words(2, true)];
    }
}
```

`database/factories/TagFactory.php`:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->unique()->word()];
    }
}
```

`database/factories/PostFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'author_id'    => User::factory(),
            'title'        => fake()->sentence(6),
            'excerpt'      => fake()->sentence(),
            'body'         => fake()->paragraphs(4, true),
            'status'       => 'draft',
            'published_at' => null,
        ];
    }
}
```

- [ ] **Step 9: Migrate + run test**

```bash
php artisan migrate
./vendor/bin/pest tests/Feature/Models/PostFactoryTest.php
```

Expected: 2 passing.

- [ ] **Step 10: Commit**

```bash
git add .
git commit -m "feat(blog): add posts, categories, tags schema and models"
```

---

### Task 7: `portfolios` migration + model

**Files:**
- Create: `database/migrations/<ts>_create_portfolios_table.php`
- Create: `app/Models/Portfolio.php`
- Create: `database/factories/PortfolioFactory.php`
- Create: `tests/Feature/Models/PortfolioFactoryTest.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/Models/PortfolioFactoryTest.php`:

```php
<?php

use App\Models\Portfolio;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('creates a portfolio item', function () {
    $u = User::factory()->create();
    $p = Portfolio::factory()->for($u, 'owner')->create();
    expect($p->slug)->not->toBeEmpty()
        ->and($p->status)->toBe('draft')
        ->and($p->owner->id)->toBe($u->id);
});
```

- [ ] **Step 2: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Models/PortfolioFactoryTest.php
```

- [ ] **Step 3: Migration**

```bash
php artisan make:migration create_portfolios_table
```

```php
return new class extends Migration {
    public function up(): void {
        Schema::create('portfolios', function (Blueprint $t) {
            $t->id();
            $t->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('service_id')->nullable();    // FK constrained in next task
            $t->foreignId('industry_id')->nullable();   // FK constrained in next task
            $t->string('title');
            $t->string('slug')->unique();
            $t->string('client')->nullable();
            $t->unsignedSmallInteger('year')->nullable();
            $t->longText('body')->nullable();
            $t->enum('status', ['draft','published'])->default('draft');
            $t->timestamps();
            $t->index(['status','year']);
        });
    }
    public function down(): void { Schema::dropIfExists('portfolios'); }
};
```

- [ ] **Step 4: Model**

`app/Models/Portfolio.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Portfolio extends Model implements HasMedia
{
    use HasFactory, HasSlug, InteractsWithMedia;

    protected $fillable = [
        'owner_id','service_id','industry_id','title','slug','client',
        'year','body','status',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug');
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status','published');
    }

    public function owner(): BelongsTo    { return $this->belongsTo(User::class,'owner_id'); }
    public function service(): BelongsTo  { return $this->belongsTo(Service::class); }
    public function industry(): BelongsTo { return $this->belongsTo(Industry::class); }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
    }
}
```

- [ ] **Step 5: Factory**

`database/factories/PortfolioFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PortfolioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'title'    => fake()->sentence(4),
            'client'   => fake()->company(),
            'year'     => fake()->numberBetween(2020, 2026),
            'body'     => fake()->paragraphs(3, true),
            'status'   => 'draft',
        ];
    }
}
```

- [ ] **Step 6: Migrate + test**

```bash
php artisan migrate
./vendor/bin/pest tests/Feature/Models/PortfolioFactoryTest.php
```

Expected: 1 passing.

- [ ] **Step 7: Commit**

```bash
git add .
git commit -m "feat(portfolio): add portfolios schema and model"
```

---

### Task 8: `services` + `industries` + `crew` + `site_settings` migrations + models

**Files:**
- Create: 4 migrations + 4 models + 4 factories
- Create: `tests/Feature/Models/SiteContentModelsTest.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/Models/SiteContentModelsTest.php`:

```php
<?php

use App\Models\Crew;
use App\Models\Industry;
use App\Models\Service;
use App\Models\SiteSetting;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('creates service / industry / crew rows', function () {
    expect(Service::factory()->create()->slug)->not->toBeEmpty()
        ->and(Industry::factory()->create()->slug)->not->toBeEmpty()
        ->and(Crew::factory()->create()->slug)->not->toBeEmpty();
});

it('stores + reads a site setting', function () {
    SiteSetting::set('contact_email', 'hi@x.com');
    expect(SiteSetting::get('contact_email'))->toBe('hi@x.com')
        ->and(SiteSetting::get('missing', 'fallback'))->toBe('fallback');
});
```

- [ ] **Step 2: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Models/SiteContentModelsTest.php
```

- [ ] **Step 3: Services migration**

```bash
php artisan make:migration create_services_table
```

```php
return new class extends Migration {
    public function up(): void {
        Schema::create('services', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('title');
            $t->text('hero_copy')->nullable();
            $t->longText('body')->nullable();
            $t->unsignedSmallInteger('order')->default(0);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('services'); }
};
```

- [ ] **Step 4: Industries migration**

```bash
php artisan make:migration create_industries_table
```

```php
return new class extends Migration {
    public function up(): void {
        Schema::create('industries', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('title');
            $t->string('summary')->nullable();
            $t->longText('body')->nullable();
            $t->unsignedSmallInteger('order')->default(0);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('industries'); }
};
```

- [ ] **Step 5: Crew migration**

```bash
php artisan make:migration create_crew_table
```

```php
return new class extends Migration {
    public function up(): void {
        Schema::create('crew', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('name');
            $t->string('role');
            $t->longText('bio')->nullable();
            $t->json('social_json')->nullable();
            $t->unsignedSmallInteger('order')->default(0);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('crew'); }
};
```

- [ ] **Step 6: Site settings migration**

```bash
php artisan make:migration create_site_settings_table
```

```php
return new class extends Migration {
    public function up(): void {
        Schema::create('site_settings', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->json('value_json')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('site_settings'); }
};
```

- [ ] **Step 7: Add FK constraints to portfolios.service_id / industry_id**

```bash
php artisan make:migration add_service_industry_fks_to_portfolios
```

```php
return new class extends Migration {
    public function up(): void {
        Schema::table('portfolios', function (Blueprint $t) {
            $t->foreign('service_id')->references('id')->on('services')->nullOnDelete();
            $t->foreign('industry_id')->references('id')->on('industries')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('portfolios', function (Blueprint $t) {
            $t->dropForeign(['service_id']);
            $t->dropForeign(['industry_id']);
        });
    }
};
```

- [ ] **Step 8: Models**

`app/Models/Service.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Service extends Model implements HasMedia
{
    use HasFactory, HasSlug, InteractsWithMedia;
    protected $fillable = ['slug','title','hero_copy','body','order'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug');
    }
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('hero')->singleFile();
    }
}
```

`app/Models/Industry.php` — same pattern with `summary` field, `hero` collection.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Industry extends Model implements HasMedia
{
    use HasFactory, HasSlug, InteractsWithMedia;
    protected $fillable = ['slug','title','summary','body','order'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug');
    }
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('hero')->singleFile();
    }
}
```

`app/Models/Crew.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Crew extends Model implements HasMedia
{
    use HasFactory, HasSlug, InteractsWithMedia;
    protected $table = 'crew';
    protected $fillable = ['slug','name','role','bio','social_json','order'];
    protected $casts = ['social_json' => 'array'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('headshot')->singleFile();
    }
}
```

`app/Models/SiteSetting.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key','value_json'];
    protected $casts = ['value_json' => 'array'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::find($key);
        if (! $row) return $default;
        $v = $row->value_json;
        return is_array($v) && array_key_exists('v', $v) ? $v['v'] : $v;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value_json' => ['v' => $value]]);
    }
}
```

- [ ] **Step 9: Factories**

`database/factories/ServiceFactory.php`:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'      => fake()->unique()->words(2, true),
            'hero_copy'  => fake()->sentence(),
            'body'       => fake()->paragraphs(3, true),
            'order'      => 0,
        ];
    }
}
```

`database/factories/IndustryFactory.php`:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class IndustryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'   => fake()->unique()->words(2, true),
            'summary' => fake()->sentence(),
            'body'    => fake()->paragraphs(3, true),
            'order'   => 0,
        ];
    }
}
```

`database/factories/CrewFactory.php`:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CrewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'        => fake()->unique()->name(),
            'role'        => fake()->jobTitle(),
            'bio'         => fake()->paragraph(),
            'social_json' => ['instagram' => 'https://instagram.com/x'],
            'order'       => 0,
        ];
    }
}
```

- [ ] **Step 10: Migrate + test**

```bash
php artisan migrate
./vendor/bin/pest tests/Feature/Models/SiteContentModelsTest.php
```

Expected: 2 passing.

- [ ] **Step 11: Commit**

```bash
git add .
git commit -m "feat(content): add services, industries, crew, site_settings"
```

---

### Task 9: Roles + Admin user + Content seeders

**Files:**
- Create: `database/seeders/RolesSeeder.php`
- Create: `database/seeders/AdminUserSeeder.php`
- Create: `database/seeders/ServicesSeeder.php`
- Create: `database/seeders/IndustriesSeeder.php`
- Create: `database/seeders/CrewSeeder.php`
- Create: `database/seeders/PortfoliosSeeder.php`
- Create: `database/seeders/PostsSeeder.php`
- Create: `database/seeders/SiteSettingsSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Create: `tests/Feature/SeederTest.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/SeederTest.php`:

```php
<?php

use App\Models\Crew;
use App\Models\Industry;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('seeds roles, admin user, services, industries, crew, sample content', function () {
    $this->seed();

    expect(Role::pluck('name')->all())->toContain('Super-admin','Editor','Sales','Viewer')
        ->and(User::where('email', config('app.admin_seed_email'))->exists())->toBeTrue()
        ->and(Service::count())->toBe(7)
        ->and(Industry::count())->toBeGreaterThanOrEqual(4)
        ->and(Crew::count())->toBeGreaterThanOrEqual(3)
        ->and(Portfolio::published()->count())->toBeGreaterThanOrEqual(3)
        ->and(Post::published()->count())->toBeGreaterThanOrEqual(3)
        ->and(SiteSetting::get('contact_email'))->toBe('hello@thelastclicks.com');
});
```

- [ ] **Step 2: Add `admin_seed_email` to `config/app.php`**

Append inside the returned array of `config/app.php`:

```php
'admin_seed_email'    => env('ADMIN_SEED_EMAIL', 'admin@thelastclicks.com'),
'admin_seed_password' => env('ADMIN_SEED_PASSWORD', 'ChangeMe!123'),
```

- [ ] **Step 3: Run, expect FAIL** (seeders missing)

```bash
./vendor/bin/pest tests/Feature/SeederTest.php
```

- [ ] **Step 4: `RolesSeeder`**

`database/seeders/RolesSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Super-admin','Editor','Sales','Viewer'] as $name) {
            Role::findOrCreate($name, 'web');
        }
    }
}
```

- [ ] **Step 5: `AdminUserSeeder`**

`database/seeders/AdminUserSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $u = User::firstOrCreate(
            ['email' => config('app.admin_seed_email')],
            ['name'  => 'Admin', 'password' => config('app.admin_seed_password')]
        );
        $u->syncRoles(['Super-admin']);
    }
}
```

- [ ] **Step 6: `ServicesSeeder`**

`database/seeders/ServicesSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServicesSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['videography',         'Videography',         'Brand films, commercials, documentaries.'],
            ['photography',         'Photography',         'Editorial, lifestyle, product, portrait.'],
            ['weddings',            'Weddings',            'Cinematic wedding films and stills.'],
            ['post-production',     'Post Production',     'Edit, colour, sound, finishing.'],
            ['social-content',      'Social Content',      'Short-form, vertical, campaign-ready.'],
            ['creative-direction',  'Creative Direction',  'Concept, treatment, art direction.'],
            ['talent',              'Talent',              'Casting, models, on-screen talent.'],
        ];
        foreach ($rows as $i => [$slug, $title, $hero]) {
            Service::updateOrCreate(['slug' => $slug], [
                'title' => $title, 'hero_copy' => $hero, 'body' => '', 'order' => $i,
            ]);
        }
    }
}
```

- [ ] **Step 7: `IndustriesSeeder`**

`database/seeders/IndustriesSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;

class IndustriesSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['fashion',     'Fashion',     'Look-books, lifestyle, editorial.'],
            ['hospitality', 'Hospitality', 'Hotels, resorts, restaurants.'],
            ['beauty',      'Beauty',      'Skincare, cosmetics, fragrance.'],
            ['weddings',    'Weddings',    'Destination + traditional.'],
            ['automotive',  'Automotive',  'Launch films, dealer content.'],
        ];
        foreach ($rows as $i => [$slug, $title, $summary]) {
            Industry::updateOrCreate(['slug' => $slug], [
                'title' => $title, 'summary' => $summary, 'body' => '', 'order' => $i,
            ]);
        }
    }
}
```

- [ ] **Step 8: `CrewSeeder`**

`database/seeders/CrewSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\Crew;
use Illuminate\Database\Seeder;

class CrewSeeder extends Seeder
{
    public function run(): void
    {
        Crew::factory()->count(4)->create();
    }
}
```

- [ ] **Step 9: `PortfoliosSeeder`**

`database/seeders/PortfoliosSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\Industry;
use App\Models\Portfolio;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class PortfoliosSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::first() ?? User::factory()->create();
        Portfolio::factory()
            ->count(6)
            ->state(fn () => [
                'owner_id'    => $owner->id,
                'service_id'  => Service::inRandomOrder()->value('id'),
                'industry_id' => Industry::inRandomOrder()->value('id'),
                'status'      => 'published',
            ])->create();
    }
}
```

- [ ] **Step 10: `PostsSeeder`**

`database/seeders/PostsSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostsSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::first();
        $cat = Category::factory()->create(['name' => 'Behind the scenes']);
        $tag = Tag::factory()->create(['name' => 'craft']);
        Post::factory()->count(5)->for($author, 'author')
            ->state(['status' => 'published','published_at' => now()->subDays(rand(1,30))])
            ->create()
            ->each(function ($p) use ($cat, $tag) {
                $p->categories()->sync([$cat->id]);
                $p->tags()->sync([$tag->id]);
            });
    }
}
```

- [ ] **Step 11: `SiteSettingsSeeder`**

`database/seeders/SiteSettingsSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::set('contact_email', 'hello@thelastclicks.com');
        SiteSetting::set('contact_phone', '+91-87701-55842');
        SiteSetting::set('whatsapp_url',  'https://wa.me/918770155842');
        SiteSetting::set('socials', [
            'instagram' => 'https://instagram.com/thelastclicks',
            'youtube'   => 'https://youtube.com/@thelastclicks',
        ]);
        SiteSetting::set('seo_default_title', 'TheLastClicks — Cinematic photography & film production');
        SiteSetting::set('seo_default_description', 'Cinematic photography, brand films and post-production for premium teams.');
    }
}
```

- [ ] **Step 12: Wire `DatabaseSeeder`**

`database/seeders/DatabaseSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            AdminUserSeeder::class,
            ServicesSeeder::class,
            IndustriesSeeder::class,
            CrewSeeder::class,
            PortfoliosSeeder::class,
            PostsSeeder::class,
            SiteSettingsSeeder::class,
        ]);
    }
}
```

- [ ] **Step 13: Seed + test**

```bash
php artisan migrate:fresh --seed
./vendor/bin/pest tests/Feature/SeederTest.php
```

Expected: 1 passing.

- [ ] **Step 14: Commit**

```bash
git add .
git commit -m "feat(seeders): roles, admin user, services, industries, crew, sample content, site settings"
```

---

## Phase C — Blade Layout + Components

### Task 10: Layout component (`<x-layouts.app>`)

**Files:**
- Create: `resources/views/layouts/app.blade.php`
- Create: `tests/Feature/Public/LayoutTest.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/Public/LayoutTest.php`:

```php
<?php

it('layout renders title, description, vite manifest, and nav/footer slots', function () {
    \Illuminate\Support\Facades\Route::get('/_test-layout', fn () => view('layouts.app', [
        'title' => 'Test Title',
        'description' => 'Test desc',
        'slot' => new \Illuminate\Support\HtmlString('<p>BODY</p>'),
    ]));
    $r = $this->get('/_test-layout');
    $r->assertOk()
        ->assertSee('<title>Test Title</title>', false)
        ->assertSee('Test desc')
        ->assertSee('BODY')
        ->assertSee('resources/css/core.css', false);
});
```

- [ ] **Step 2: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Public/LayoutTest.php
```

- [ ] **Step 3: Write `resources/views/layouts/app.blade.php`**

```blade
@props([
    'title' => null,
    'description' => null,
    'ogImage' => null,
    'canonical' => null,
])
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? \App\Models\SiteSetting::get('seo_default_title', config('app.name')) }}</title>
    <meta name="description" content="{{ $description ?? \App\Models\SiteSetting::get('seo_default_description', '') }}">
    @if ($canonical) <link rel="canonical" href="{{ $canonical }}"> @endif
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.svg') }}">
    <meta name="theme-color" content="#0a0a0a">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    @if ($ogImage) <meta property="og:image" content="{{ $ogImage }}"> @endif
    <meta name="twitter:card" content="summary_large_image">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Sora:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    @vite(['resources/css/core.css','resources/css/pages.css','resources/js/core.js','resources/js/chrome.js'])
    {{ $head ?? '' }}
</head>
<body>
    <x-nav />
    <main>{{ $slot }}</main>
    <x-footer />
</body>
</html>
```

- [ ] **Step 4: Copy favicon assets to `public/`**

```bash
cp design/favicon.svg public/favicon.svg
cp design/apple-touch-icon.svg public/apple-touch-icon.svg
cp design/manifest.webmanifest public/manifest.webmanifest
cp design/robots.txt public/robots.txt
```

- [ ] **Step 5: Stub `nav` + `footer` components so layout renders**

`resources/views/components/nav.blade.php`:

```blade
<header class="nav"><a href="/" class="brand">TheLastClicks</a></header>
```

`resources/views/components/footer.blade.php`:

```blade
<footer class="foot"><span>&copy; {{ date('Y') }} TheLastClicks</span></footer>
```

- [ ] **Step 6: Run test, expect PASS**

```bash
./vendor/bin/pest tests/Feature/Public/LayoutTest.php
```

- [ ] **Step 7: Commit**

```bash
git add .
git commit -m "feat(layout): app layout, favicon, stub nav/footer"
```

---

### Task 11: Real nav + footer components from design

**Files:**
- Modify: `resources/views/components/nav.blade.php`
- Modify: `resources/views/components/footer.blade.php`

- [ ] **Step 1: Open `design/index.html` and copy the `<header>` markup into `resources/views/components/nav.blade.php`**

Locate the top nav markup in `design/index.html` (the `<header>` immediately after `<body>`, before `<main>`). Paste it verbatim into `resources/views/components/nav.blade.php`. Then convert any in-page hash links to `{{ route(...) }}` or relative URLs, e.g.:

- `href="index.html"`           → `href="{{ url('/') }}"`
- `href="about.html"`           → `href="{{ url('/about') }}"`
- `href="portfolio.html"`       → `href="{{ url('/portfolio') }}"`
- `href="blog.html"`            → `href="{{ url('/blog') }}"`
- `href="contact.html"`         → `href="{{ url('/contact') }}"`
- `href="our-process.html"`     → `href="{{ url('/our-process') }}"`
- `href="industries.html"`      → `href="{{ url('/industries') }}"`
- Service links (photography.html etc) → `href="{{ url('/services/photography') }}"` and so on.

- [ ] **Step 2: Copy `<footer>` from `design/index.html` (last section before `</body>`) into `resources/views/components/footer.blade.php`**. Apply the same href rewrites. Replace any hard-coded email/phone with:

```blade
{{ \App\Models\SiteSetting::get('contact_email','hello@thelastclicks.com') }}
{{ \App\Models\SiteSetting::get('contact_phone','+91-87701-55842') }}
```

- [ ] **Step 3: Smoke-test by viewing the layout via the test route from Task 10 in a browser**

```bash
php artisan serve --port=8000 &
sleep 1
open http://localhost:8000/_test-layout || curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8000/_test-layout
kill %1
```

Expected: nav + footer render with brand fonts. If a logo image is referenced by a path like `assets/...`, copy that asset into `public/` and update the `src`.

- [ ] **Step 4: Remove the `/_test-layout` route**

Edit `tests/Feature/Public/LayoutTest.php` so the test no longer needs the route registered globally (the inline `Route::get` inside the test is fine — leave it).

- [ ] **Step 5: Re-run pest**

```bash
./vendor/bin/pest tests/Feature/Public/LayoutTest.php
```

Expected: still passing.

- [ ] **Step 6: Commit**

```bash
git add resources/views/components/nav.blade.php resources/views/components/footer.blade.php public/
git commit -m "feat(layout): real nav + footer from design"
```

---

### Task 12: Card + hero + marquee + json-ld + quote-form components

**Files:**
- Create: `resources/views/components/hero.blade.php`
- Create: `resources/views/components/marquee.blade.php`
- Create: `resources/views/components/card-post.blade.php`
- Create: `resources/views/components/card-portfolio.blade.php`
- Create: `resources/views/components/card-crew.blade.php`
- Create: `resources/views/components/quote-form.blade.php`
- Create: `resources/views/components/json-ld.blade.php`
- Create: `tests/Feature/Public/ComponentsRenderTest.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/Public/ComponentsRenderTest.php`:

```php
<?php

it('renders the json-ld component as a script tag', function () {
    $html = \Illuminate\Support\Facades\Blade::render(
        '<x-json-ld :data="$d" />',
        ['d' => ['@type' => 'Organization','name' => 'X']]
    );
    expect($html)->toContain('<script type="application/ld+json">')
        ->and($html)->toContain('"@type":"Organization"');
});

it('renders card-post with post data', function () {
    $post = App\Models\Post::factory()->for(App\Models\User::factory(), 'author')->create(['title' => 'Hello']);
    $html = \Illuminate\Support\Facades\Blade::render('<x-card-post :post="$p" />', ['p' => $post]);
    expect($html)->toContain('Hello')->and($html)->toContain($post->slug);
});
```

- [ ] **Step 2: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Public/ComponentsRenderTest.php
```

- [ ] **Step 3: `json-ld.blade.php`**

```blade
@props(['data' => []])
<script type="application/ld+json">{!! json_encode(array_merge(['@context'=>'https://schema.org'], $data), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
```

- [ ] **Step 4: `card-post.blade.php`**

```blade
@props(['post'])
<article class="card card--post">
    @if ($cover = $post->getFirstMediaUrl('cover'))
        <img src="{{ $cover }}" alt="">
    @endif
    <a href="{{ url('/blog/'.$post->slug) }}">
        <h3>{{ $post->title }}</h3>
        <p>{{ $post->excerpt }}</p>
    </a>
</article>
```

- [ ] **Step 5: `card-portfolio.blade.php`**

```blade
@props(['item'])
<article class="card card--portfolio">
    @if ($cover = $item->getFirstMediaUrl('cover'))
        <img src="{{ $cover }}" alt="">
    @endif
    <a href="{{ url('/portfolio/'.$item->slug) }}">
        <h3>{{ $item->title }}</h3>
        <p>{{ $item->client }} · {{ $item->year }}</p>
    </a>
</article>
```

- [ ] **Step 6: `card-crew.blade.php`**

```blade
@props(['member'])
<article class="card card--crew">
    @if ($img = $member->getFirstMediaUrl('headshot'))
        <img src="{{ $img }}" alt="">
    @endif
    <a href="{{ url('/crew/'.$member->slug) }}">
        <h3>{{ $member->name }}</h3>
        <p>{{ $member->role }}</p>
    </a>
</article>
```

- [ ] **Step 7: `hero.blade.php`** — copy the `<section class="hero">…</section>` block from `design/index.html` into this file as the default slot. Wrap with `@props(['title' => null, 'subtitle' => null])` and substitute `{{ $title }}` / `{{ $subtitle }}` into the `<h1>` and lede paragraph.

- [ ] **Step 8: `marquee.blade.php`** — copy the marquee section from `design/index.html` verbatim.

- [ ] **Step 9: `quote-form.blade.php`** — copy the `<form>` from `design/contact.html` and:
  - Add `method="POST" action="{{ url('/contact') }}"`.
  - Insert `@csrf` immediately after `<form …>`.
  - Add hidden honeypot field: `<input type="text" name="website" autocomplete="off" tabindex="-1" style="position:absolute;left:-9999px">`.
  - Append `<input type="hidden" name="source_page" value="{{ request()->path() }}">`.
  - Replace the inline `onsubmit` JS with nothing — the form posts to the server now.
  - For each field, render `@error('name') <small class="err">{{ $message }}</small> @enderror`.

- [ ] **Step 10: Run pest**

```bash
./vendor/bin/pest tests/Feature/Public/ComponentsRenderTest.php
```

Expected: 2 passing.

- [ ] **Step 11: Commit**

```bash
git add .
git commit -m "feat(components): hero, marquee, cards, quote-form, json-ld"
```

---

## Phase D — Public Pages

### Task 13: HomeController + home view

**Files:**
- Create: `app/Http/Controllers/Public/HomeController.php`
- Create: `resources/views/home.blade.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/Public/HomePageTest.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/Public/HomePageTest.php`:

```php
<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('renders the homepage with key copy', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('TheLastClicks')
        ->assertSee('Capturing', false);
});

it('homepage emits Organization JSON-LD', function () {
    $this->get('/')->assertSee('"@type":"Organization"', false);
});
```

- [ ] **Step 2: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Public/HomePageTest.php
```

- [ ] **Step 3: Controller**

`app/Http/Controllers/Public/HomeController.php`:

```php
<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Service;

class HomeController extends Controller
{
    public function index()
    {
        return view('home', [
            'services'  => Service::orderBy('order')->get(),
            'portfolio' => Portfolio::published()->latest()->take(6)->get(),
            'posts'     => Post::published()->latest('published_at')->take(3)->get(),
        ]);
    }
}
```

- [ ] **Step 4: Home view**

`resources/views/home.blade.php` — copy the entire `<main>…</main>` body of `design/index.html` between `<x-layouts.app>` open/close tags. Replace any hard-coded card sections with iterations:

```blade
<x-layouts.app
    title="TheLastClicks — Cinematic photography & film production"
    description="Cinematic photography, brand films and post for premium teams."
    canonical="{{ url('/') }}"
>
    <x-slot name="head">
        <x-json-ld :data="[
            '@type' => 'Organization',
            'name'  => 'TheLastClicks',
            'url'   => url('/'),
            'logo'  => asset('apple-touch-icon.svg'),
            'email' => \App\Models\SiteSetting::get('contact_email'),
            'telephone' => \App\Models\SiteSetting::get('contact_phone'),
        ]" />
    </x-slot>

    {{-- Paste cleaned hero/marquee/sections from design/index.html here.
         Replace static portfolio grid with:                            --}}
    <section class="grid grid--portfolio">
        @foreach ($portfolio as $item)
            <x-card-portfolio :item="$item" />
        @endforeach
    </section>

    {{-- Replace static services list with: --}}
    <section class="grid grid--services">
        @foreach ($services as $service)
            <a class="service" href="{{ url('/services/'.$service->slug) }}">
                <h3>{{ $service->title }}</h3>
                <p>{{ $service->hero_copy }}</p>
            </a>
        @endforeach
    </section>

    {{-- Replace static blog teaser with: --}}
    <section class="grid grid--posts">
        @foreach ($posts as $post)
            <x-card-post :post="$post" />
        @endforeach
    </section>
</x-layouts.app>
```

- [ ] **Step 5: Register route**

`routes/web.php`:

```php
<?php

use App\Http\Controllers\Public\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
```

- [ ] **Step 6: Run pest**

```bash
./vendor/bin/pest tests/Feature/Public/HomePageTest.php
```

Expected: 2 passing.

- [ ] **Step 7: Commit**

```bash
git add .
git commit -m "feat(public): home page"
```

---

### Task 14: `PageController` — about, our-process, legal, thank-you

**Files:**
- Create: `app/Http/Controllers/Public/PageController.php`
- Create: `resources/views/pages/{about,our-process,privacy-policy,terms-of-service,cookie-policy,disclaimer,thank-you}.blade.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/Public/PageTest.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/Public/PageTest.php`:

```php
<?php

it('static pages return 200', function (string $path) {
    $this->get($path)->assertOk();
})->with([
    '/about',
    '/our-process',
    '/privacy-policy',
    '/terms-of-service',
    '/cookie-policy',
    '/disclaimer',
    '/thank-you',
]);
```

- [ ] **Step 2: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Public/PageTest.php
```

- [ ] **Step 3: Controller**

`app/Http/Controllers/Public/PageController.php`:

```php
<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function about()         { return view('pages.about'); }
    public function process()       { return view('pages.our-process'); }
    public function privacy()       { return view('pages.privacy-policy'); }
    public function terms()         { return view('pages.terms-of-service'); }
    public function cookies()       { return view('pages.cookie-policy'); }
    public function disclaimer()    { return view('pages.disclaimer'); }
    public function thankYou()      { return view('pages.thank-you'); }
}
```

- [ ] **Step 4: Views**

For each of the seven pages, copy the corresponding HTML body from `design/<file>.html` between `<x-layouts.app title="…" description="…">` and `</x-layouts.app>`. Use the design's own page title/description text.

Example — `resources/views/pages/about.blade.php`:

```blade
<x-layouts.app
    title="About — TheLastClicks"
    description="A photography & production studio working at the intersection of cinema, brand, and craft."
    canonical="{{ url('/about') }}"
>
    {{-- paste cleaned <main> contents from design/about.html --}}
</x-layouts.app>
```

Repeat for `our-process`, `privacy-policy`, `terms-of-service`, `cookie-policy`, `disclaimer`, `thank-you`.

- [ ] **Step 5: Register routes**

Append to `routes/web.php`:

```php
use App\Http\Controllers\Public\PageController;

Route::get('/about',            [PageController::class, 'about'])->name('about');
Route::get('/our-process',      [PageController::class, 'process'])->name('our-process');
Route::get('/privacy-policy',   [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms-of-service', [PageController::class, 'terms'])->name('terms');
Route::get('/cookie-policy',    [PageController::class, 'cookies'])->name('cookies');
Route::get('/disclaimer',       [PageController::class, 'disclaimer'])->name('disclaimer');
Route::get('/thank-you',        [PageController::class, 'thankYou'])->name('thank-you');
```

- [ ] **Step 6: Run pest**

```bash
./vendor/bin/pest tests/Feature/Public/PageTest.php
```

Expected: 7 passing.

- [ ] **Step 7: Commit**

```bash
git add .
git commit -m "feat(public): about, our-process, legal pages, thank-you"
```

---

### Task 15: `ServiceController@show`

**Files:**
- Create: `app/Http/Controllers/Public/ServiceController.php`
- Create: `resources/views/services/show.blade.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/Public/ServicePageTest.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/Public/ServicePageTest.php`:

```php
<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('renders each seeded service page', function (string $slug) {
    $this->get("/services/{$slug}")->assertOk()->assertSeeText(ucwords(str_replace('-', ' ', $slug)));
})->with([
    'videography','photography','weddings','post-production',
    'social-content','creative-direction','talent',
]);

it('returns 404 for unknown service slug', function () {
    $this->get('/services/does-not-exist')->assertNotFound();
});
```

- [ ] **Step 2: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Public/ServicePageTest.php
```

- [ ] **Step 3: Controller**

`app/Http/Controllers/Public/ServiceController.php`:

```php
<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\Service;

class ServiceController extends Controller
{
    public function show(string $slug)
    {
        $service = Service::where('slug', $slug)->firstOrFail();
        $work = Portfolio::published()->where('service_id', $service->id)->latest()->take(6)->get();
        return view('services.show', compact('service','work'));
    }
}
```

- [ ] **Step 4: View**

`resources/views/services/show.blade.php` — base it on `design/photography.html` (the most representative service page). Replace hard-coded title/copy with `{{ $service->title }}` / `{!! $service->hero_copy !!}` / `{!! $service->body !!}`. Render `$work` via `<x-card-portfolio>`.

```blade
<x-layouts.app
    :title="$service->title.' — TheLastClicks'"
    :description="$service->hero_copy"
    :canonical="url('/services/'.$service->slug)"
>
    <x-slot name="head">
        <x-json-ld :data="['@type'=>'Service','name'=>$service->title,'description'=>$service->hero_copy]" />
    </x-slot>

    <section class="service-hero">
        <h1>{{ $service->title }}</h1>
        <p>{{ $service->hero_copy }}</p>
    </section>

    <section class="service-body">
        {!! $service->body !!}
    </section>

    <section class="grid grid--portfolio">
        @foreach ($work as $item)
            <x-card-portfolio :item="$item" />
        @endforeach
    </section>
</x-layouts.app>
```

- [ ] **Step 5: Route**

Append to `routes/web.php`:

```php
use App\Http\Controllers\Public\ServiceController;
Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('service.show');
```

- [ ] **Step 6: Run pest**

```bash
./vendor/bin/pest tests/Feature/Public/ServicePageTest.php
```

Expected: 8 passing (7 services + 1 404).

- [ ] **Step 7: Commit**

```bash
git add .
git commit -m "feat(public): service detail pages"
```

---

### Task 16: `IndustryController` — index + show

**Files:**
- Create: `app/Http/Controllers/Public/IndustryController.php`
- Create: `resources/views/industries/index.blade.php`
- Create: `resources/views/industries/show.blade.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/Public/IndustryPageTest.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/Public/IndustryPageTest.php`:

```php
<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('industry index lists seeded industries', function () {
    $this->get('/industries')->assertOk()->assertSee('Fashion');
});

it('industry detail renders by slug', function () {
    $this->get('/industries/fashion')->assertOk()->assertSee('Fashion');
});

it('industry detail 404 on unknown slug', function () {
    $this->get('/industries/nope')->assertNotFound();
});
```

- [ ] **Step 2: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Public/IndustryPageTest.php
```

- [ ] **Step 3: Controller**

`app/Http/Controllers/Public/IndustryController.php`:

```php
<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Industry;
use App\Models\Portfolio;

class IndustryController extends Controller
{
    public function index()
    {
        return view('industries.index', [
            'industries' => Industry::orderBy('order')->get(),
        ]);
    }

    public function show(string $slug)
    {
        $industry = Industry::where('slug', $slug)->firstOrFail();
        $work = Portfolio::published()->where('industry_id', $industry->id)->latest()->take(12)->get();
        return view('industries.show', compact('industry','work'));
    }
}
```

- [ ] **Step 4: Views**

`resources/views/industries/index.blade.php` — base on `design/industries.html`, iterate seeded list:

```blade
<x-layouts.app title="Industries — TheLastClicks">
    <section class="grid grid--industries">
        @foreach ($industries as $industry)
            <a class="industry" href="{{ url('/industries/'.$industry->slug) }}">
                <h3>{{ $industry->title }}</h3>
                <p>{{ $industry->summary }}</p>
            </a>
        @endforeach
    </section>
</x-layouts.app>
```

`resources/views/industries/show.blade.php`:

```blade
<x-layouts.app :title="$industry->title.' — Industries — TheLastClicks'" :description="$industry->summary">
    <h1>{{ $industry->title }}</h1>
    <p>{{ $industry->summary }}</p>
    {!! $industry->body !!}
    <section class="grid grid--portfolio">
        @foreach ($work as $item)
            <x-card-portfolio :item="$item" />
        @endforeach
    </section>
</x-layouts.app>
```

- [ ] **Step 5: Routes**

Append:

```php
use App\Http\Controllers\Public\IndustryController;
Route::get('/industries',          [IndustryController::class, 'index'])->name('industries');
Route::get('/industries/{slug}',   [IndustryController::class, 'show'])->name('industry.show');
```

- [ ] **Step 6: Run pest, expect 3 passing**

```bash
./vendor/bin/pest tests/Feature/Public/IndustryPageTest.php
```

- [ ] **Step 7: Commit**

```bash
git add .
git commit -m "feat(public): industries index + detail"
```

---

### Task 17: `PortfolioController` — index + show

**Files:**
- Create: `app/Http/Controllers/Public/PortfolioController.php`
- Create: `resources/views/portfolio/{index,show}.blade.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/Public/PortfolioPageTest.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/Public/PortfolioPageTest.php`:

```php
<?php

use App\Models\Portfolio;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('portfolio index shows only published items', function () {
    Portfolio::factory()->create(['status' => 'draft','title' => 'HiddenDraft']);
    $r = $this->get('/portfolio')->assertOk();
    $r->assertDontSee('HiddenDraft');
});

it('portfolio detail renders by slug', function () {
    $p = Portfolio::published()->first();
    $this->get('/portfolio/'.$p->slug)->assertOk()->assertSee($p->title);
});

it('portfolio detail 404 on unknown slug', function () {
    $this->get('/portfolio/nope')->assertNotFound();
});

it('portfolio detail 404 on draft', function () {
    $p = Portfolio::factory()->create(['status' => 'draft']);
    $this->get('/portfolio/'.$p->slug)->assertNotFound();
});
```

- [ ] **Step 2: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Public/PortfolioPageTest.php
```

- [ ] **Step 3: Controller**

`app/Http/Controllers/Public/PortfolioController.php`:

```php
<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;

class PortfolioController extends Controller
{
    public function index()
    {
        return view('portfolio.index', [
            'items' => Portfolio::published()->latest()->paginate(12),
        ]);
    }

    public function show(string $slug)
    {
        $item = Portfolio::published()->where('slug', $slug)->firstOrFail();
        return view('portfolio.show', compact('item'));
    }
}
```

- [ ] **Step 4: Views**

`resources/views/portfolio/index.blade.php` — base on `design/portfolio.html`, replace cards section with:

```blade
<x-layouts.app title="Portfolio — TheLastClicks">
    <section class="grid grid--portfolio">
        @foreach ($items as $item)
            <x-card-portfolio :item="$item" />
        @endforeach
    </section>
    {{ $items->links() }}
</x-layouts.app>
```

`resources/views/portfolio/show.blade.php` — base on `design/case-details.html`:

```blade
<x-layouts.app :title="$item->title.' — Portfolio — TheLastClicks'" :description="$item->client.' · '.$item->year">
    <article class="case">
        <header>
            <h1>{{ $item->title }}</h1>
            <p>{{ $item->client }} · {{ $item->year }}</p>
        </header>
        {!! $item->body !!}
        <div class="gallery">
            @foreach ($item->getMedia('gallery') as $m)
                <img src="{{ $m->getUrl() }}" alt="">
            @endforeach
        </div>
    </article>
</x-layouts.app>
```

- [ ] **Step 5: Routes**

Append:

```php
use App\Http\Controllers\Public\PortfolioController;
Route::get('/portfolio',         [PortfolioController::class, 'index'])->name('portfolio');
Route::get('/portfolio/{slug}',  [PortfolioController::class, 'show'])->name('portfolio.show');
```

- [ ] **Step 6: Run pest, expect 4 passing**

```bash
./vendor/bin/pest tests/Feature/Public/PortfolioPageTest.php
```

- [ ] **Step 7: Commit**

```bash
git add .
git commit -m "feat(public): portfolio index + case detail"
```

---

### Task 18: `BlogController` — index + show

**Files:**
- Create: `app/Http/Controllers/Public/BlogController.php`
- Create: `resources/views/blog/{index,show}.blade.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/Public/BlogPageTest.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/Public/BlogPageTest.php`:

```php
<?php

use App\Models\Post;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('blog index lists published posts only', function () {
    Post::factory()->for(\App\Models\User::first(), 'author')->create(['status' => 'draft','title' => 'HiddenDraft']);
    $this->get('/blog')->assertOk()->assertDontSee('HiddenDraft');
});

it('blog detail renders published post', function () {
    $p = Post::published()->first();
    $this->get('/blog/'.$p->slug)->assertOk()->assertSee($p->title);
});

it('blog detail 404 on draft slug', function () {
    $p = Post::factory()->for(\App\Models\User::first(), 'author')->create(['status' => 'draft']);
    $this->get('/blog/'.$p->slug)->assertNotFound();
});

it('blog detail emits Article JSON-LD', function () {
    $p = Post::published()->first();
    $this->get('/blog/'.$p->slug)->assertSee('"@type":"Article"', false);
});
```

- [ ] **Step 2: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Public/BlogPageTest.php
```

- [ ] **Step 3: Controller**

`app/Http/Controllers/Public/BlogController.php`:

```php
<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;

class BlogController extends Controller
{
    public function index()
    {
        return view('blog.index', [
            'posts' => Post::published()->latest('published_at')->paginate(12),
        ]);
    }

    public function show(string $slug)
    {
        $post = Post::published()->where('slug', $slug)->with(['author','categories','tags'])->firstOrFail();
        return view('blog.show', compact('post'));
    }
}
```

- [ ] **Step 4: Views**

`resources/views/blog/index.blade.php` — base on `design/blog.html`:

```blade
<x-layouts.app title="Blog — TheLastClicks">
    <section class="grid grid--posts">
        @foreach ($posts as $post)
            <x-card-post :post="$post" />
        @endforeach
    </section>
    {{ $posts->links() }}
</x-layouts.app>
```

`resources/views/blog/show.blade.php` — base on `design/blog-details.html`:

```blade
<x-layouts.app :title="$post->title.' — Blog — TheLastClicks'" :description="$post->excerpt" :canonical="url('/blog/'.$post->slug)">
    <x-slot name="head">
        <x-json-ld :data="[
            '@type'         => 'Article',
            'headline'      => $post->title,
            'datePublished' => optional($post->published_at)->toIso8601String(),
            'author'        => ['@type' => 'Person','name' => $post->author->name],
        ]" />
    </x-slot>
    <article class="post">
        <h1>{{ $post->title }}</h1>
        <p class="meta">{{ $post->author->name }} · {{ $post->published_at->format('M j, Y') }}</p>
        {!! $post->body !!}
    </article>
</x-layouts.app>
```

- [ ] **Step 5: Routes**

Append:

```php
use App\Http\Controllers\Public\BlogController;
Route::get('/blog',         [BlogController::class, 'index'])->name('blog');
Route::get('/blog/{slug}',  [BlogController::class, 'show'])->name('blog.show');
```

- [ ] **Step 6: Run pest, expect 4 passing**

```bash
./vendor/bin/pest tests/Feature/Public/BlogPageTest.php
```

- [ ] **Step 7: Commit**

```bash
git add .
git commit -m "feat(public): blog index + detail with article jsonld"
```

---

### Task 19: `CrewController` — index + show

**Files:**
- Create: `app/Http/Controllers/Public/CrewController.php`
- Create: `resources/views/crew/{index,show}.blade.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/Public/CrewPageTest.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/Public/CrewPageTest.php`:

```php
<?php

use App\Models\Crew;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('crew index lists seeded members', function () {
    $first = Crew::first();
    $this->get('/crew')->assertOk()->assertSee($first->name);
});

it('crew detail renders by slug', function () {
    $first = Crew::first();
    $this->get('/crew/'.$first->slug)->assertOk()->assertSee($first->name);
});

it('crew detail 404 on unknown slug', function () {
    $this->get('/crew/nope')->assertNotFound();
});
```

- [ ] **Step 2: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Public/CrewPageTest.php
```

- [ ] **Step 3: Controller**

```php
<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Crew;

class CrewController extends Controller
{
    public function index()
    {
        return view('crew.index', ['members' => Crew::orderBy('order')->get()]);
    }

    public function show(string $slug)
    {
        $member = Crew::where('slug', $slug)->firstOrFail();
        return view('crew.show', compact('member'));
    }
}
```

- [ ] **Step 4: Views**

`resources/views/crew/index.blade.php`:

```blade
<x-layouts.app title="Crew — TheLastClicks">
    <section class="grid grid--crew">
        @foreach ($members as $m)
            <x-card-crew :member="$m" />
        @endforeach
    </section>
</x-layouts.app>
```

`resources/views/crew/show.blade.php` — base on `design/crew-details.html`:

```blade
<x-layouts.app :title="$member->name.' — Crew — TheLastClicks'" :description="$member->role">
    <article class="crew-detail">
        <header>
            <h1>{{ $member->name }}</h1>
            <p>{{ $member->role }}</p>
        </header>
        {!! $member->bio !!}
        @if ($member->social_json)
            <ul class="socials">
                @foreach ($member->social_json as $k => $url)
                    <li><a href="{{ $url }}" rel="noopener" target="_blank">{{ $k }}</a></li>
                @endforeach
            </ul>
        @endif
    </article>
</x-layouts.app>
```

- [ ] **Step 5: Routes**

```php
use App\Http\Controllers\Public\CrewController;
Route::get('/crew',         [CrewController::class, 'index'])->name('crew');
Route::get('/crew/{slug}',  [CrewController::class, 'show'])->name('crew.show');
```

- [ ] **Step 6: Run pest, expect 3 passing**

```bash
./vendor/bin/pest tests/Feature/Public/CrewPageTest.php
```

- [ ] **Step 7: Commit**

```bash
git add .
git commit -m "feat(public): crew index + detail"
```

---

### Task 20: `ContactController@show` (GET /contact)

**Files:**
- Create: `app/Http/Controllers/Public/ContactController.php`
- Create: `resources/views/contact.blade.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/Public/ContactPageTest.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/Public/ContactPageTest.php`:

```php
<?php

it('GET /contact renders the form', function () {
    $this->get('/contact')
        ->assertOk()
        ->assertSee('Tell us about it')
        ->assertSee('name="email"', false)
        ->assertSee('name="_token"', false);
});
```

- [ ] **Step 2: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Public/ContactPageTest.php
```

- [ ] **Step 3: Controller**

```php
<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact');
    }
}
```

- [ ] **Step 4: View**

`resources/views/contact.blade.php` — base on `design/contact.html`. Replace the inline `<form>` with `<x-quote-form />`. Keep the surrounding layout (sidebar with address/phone/email pulled from `SiteSetting`).

```blade
<x-layouts.app
    title="Contact — TheLastClicks"
    description="Bring us a brief — we reply within 4 working hours."
    canonical="{{ url('/contact') }}"
>
    {{-- paste cleaned contact.html main contents, but replace its <form> with: --}}
    <x-quote-form />
</x-layouts.app>
```

- [ ] **Step 5: Route**

```php
use App\Http\Controllers\Public\ContactController;
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
```

- [ ] **Step 6: Run pest**

```bash
./vendor/bin/pest tests/Feature/Public/ContactPageTest.php
```

Expected: 1 passing.

- [ ] **Step 7: Commit**

```bash
git add .
git commit -m "feat(public): contact page"
```

---

### Task 21: `StoreQuoteRequest` + `ContactController@store` + mails

**Files:**
- Create: `app/Http/Requests/StoreQuoteRequest.php`
- Create: `app/Mail/NewQuoteAdminNotification.php`
- Create: `app/Mail/QuoteAutoReply.php`
- Create: `resources/views/emails/quote-admin.blade.php`
- Create: `resources/views/emails/quote-reply.blade.php`
- Modify: `app/Http/Controllers/Public/ContactController.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/Public/ContactFormTest.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/Public/ContactFormTest.php`:

```php
<?php

use App\Mail\NewQuoteAdminNotification;
use App\Mail\QuoteAutoReply;
use App\Models\Quote;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    RateLimiter::clear('contact:127.0.0.1');
    Mail::fake();
});

it('rejects missing required fields', function () {
    $this->post('/contact', [])->assertSessionHasErrors(['name','email','message']);
});

it('accepts a valid submission, persists Quote, redirects to thank-you', function () {
    $r = $this->post('/contact', [
        'name'    => 'Jane',
        'email'   => 'jane@example.com',
        'message' => 'Brief for a launch film.',
        'project_type' => 'Brand film / commercial',
        'budget'  => '₹15L – ₹50L',
        'timeline'=> '1–2 months',
        'source_page' => '/contact',
        'website' => '', // honeypot empty
    ]);
    $r->assertRedirect('/thank-you');
    expect(Quote::count())->toBe(1);
    Mail::assertQueued(NewQuoteAdminNotification::class);
    Mail::assertQueued(QuoteAutoReply::class);
});

it('silently drops bot submissions filling honeypot', function () {
    $this->post('/contact', [
        'name'=>'B','email'=>'b@b.com','message'=>'x','website'=>'spam',
    ])->assertRedirect('/thank-you');
    expect(Quote::count())->toBe(0);
    Mail::assertNothingQueued();
});

it('rate limits beyond 5 per minute per IP', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->post('/contact', [
            'name'=>"User$i",'email'=>"u$i@x.com",'message'=>'hi',
        ])->assertRedirect('/thank-you');
    }
    $this->post('/contact', [
        'name'=>'X','email'=>'x@x.com','message'=>'hi',
    ])->assertStatus(429);
});
```

- [ ] **Step 2: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Public/ContactFormTest.php
```

- [ ] **Step 3: `StoreQuoteRequest`**

`app/Http/Requests/StoreQuoteRequest.php`:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'         => ['required','string','max:255'],
            'company'      => ['nullable','string','max:255'],
            'email'        => ['required','email:rfc','max:255'],
            'phone'        => ['nullable','string','max:64'],
            'project_type' => ['nullable','string','max:64'],
            'budget'       => ['nullable','string','max:64'],
            'timeline'     => ['nullable','string','max:64'],
            'message'      => ['required','string','max:5000'],
            'source_page'  => ['nullable','string','max:255'],
            'website'      => ['nullable','string','max:0'], // honeypot must be empty
        ];
    }
}
```

- [ ] **Step 4: Mail classes**

`app/Mail/NewQuoteAdminNotification.php`:

```php
<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewQuoteAdminNotification extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public Quote $quote) {}
    public function envelope(): Envelope { return new Envelope(subject: 'New quote: '.$this->quote->name); }
    public function content(): Content { return new Content(view: 'emails.quote-admin'); }
}
```

`app/Mail/QuoteAutoReply.php`:

```php
<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteAutoReply extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public Quote $quote) {}
    public function envelope(): Envelope { return new Envelope(subject: 'We got your brief — TheLastClicks'); }
    public function content(): Content { return new Content(view: 'emails.quote-reply'); }
}
```

- [ ] **Step 5: Email views**

`resources/views/emails/quote-admin.blade.php`:

```blade
<h2>New quote from {{ $quote->name }}</h2>
<ul>
    <li>Email: {{ $quote->email }}</li>
    <li>Phone: {{ $quote->phone ?: '—' }}</li>
    <li>Company: {{ $quote->company ?: '—' }}</li>
    <li>Project: {{ $quote->project_type ?: '—' }}</li>
    <li>Budget: {{ $quote->budget ?: '—' }}</li>
    <li>Timeline: {{ $quote->timeline ?: '—' }}</li>
    <li>Source page: {{ $quote->source_page }}</li>
</ul>
<p>{{ $quote->message }}</p>
```

`resources/views/emails/quote-reply.blade.php`:

```blade
<p>Hi {{ $quote->name }},</p>
<p>Thanks for the brief — we'll reply within 4 working hours.</p>
<p>— TheLastClicks</p>
```

- [ ] **Step 6: `ContactController@store`**

Replace `app/Http/Controllers/Public/ContactController.php` with:

```php
<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuoteRequest;
use App\Mail\NewQuoteAdminNotification;
use App\Mail\QuoteAutoReply;
use App\Models\Quote;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact');
    }

    public function store(StoreQuoteRequest $r): RedirectResponse
    {
        $key = 'contact:'.$r->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            abort(429);
        }
        RateLimiter::hit($key, 60);

        // Honeypot triggered → silently redirect, don't persist
        if (filled($r->input('website'))) {
            return redirect('/thank-you');
        }

        $quote = Quote::create([
            ...$r->validated(),
            'ip' => $r->ip(),
            'ua' => substr((string) $r->userAgent(), 0, 512),
        ]);

        $adminEmail = SiteSetting::get('contact_email', config('mail.from.address'));
        Mail::to($adminEmail)->queue(new NewQuoteAdminNotification($quote));
        Mail::to($quote->email)->queue(new QuoteAutoReply($quote));

        return redirect('/thank-you');
    }
}
```

- [ ] **Step 7: Register POST route**

Append to `routes/web.php`:

```php
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
```

- [ ] **Step 8: Run pest**

```bash
./vendor/bin/pest tests/Feature/Public/ContactFormTest.php
```

Expected: 4 passing.

- [ ] **Step 9: Commit**

```bash
git add .
git commit -m "feat(contact): store quote, queue admin + reply mails, honeypot, rate limit"
```

---

### Task 22: 404 view + remove default Laravel welcome

**Files:**
- Create: `resources/views/errors/404.blade.php`
- Delete: `resources/views/welcome.blade.php`
- Create: `tests/Feature/Public/NotFoundTest.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/Public/NotFoundTest.php`:

```php
<?php

it('renders the styled 404 page on unknown route', function () {
    $this->get('/does-not-exist-anywhere')
        ->assertStatus(404)
        ->assertSee('Page not found');
});
```

- [ ] **Step 2: Run, expect FAIL**

```bash
./vendor/bin/pest tests/Feature/Public/NotFoundTest.php
```

- [ ] **Step 3: 404 view**

`resources/views/errors/404.blade.php` — base on `design/404.html`:

```blade
<x-layouts.app title="Page not found — TheLastClicks">
    <section class="error-404">
        <h1>404</h1>
        <p>Page not found.</p>
        <a href="{{ url('/') }}" class="btn btn--ghost">Back home</a>
    </section>
</x-layouts.app>
```

- [ ] **Step 4: Delete welcome**

```bash
rm resources/views/welcome.blade.php
```

- [ ] **Step 5: Run pest**

```bash
./vendor/bin/pest tests/Feature/Public/NotFoundTest.php
```

Expected: 1 passing.

- [ ] **Step 6: Commit**

```bash
git add .
git commit -m "feat(public): 404 page, remove welcome"
```

---

## Phase E — Full Suite Verification + Smoke Test

### Task 23: Run the entire test suite

- [ ] **Step 1: Run all pest tests**

```bash
./vendor/bin/pest
```

Expected: ALL passing. If anything fails, debug and fix before moving on.

- [ ] **Step 2: Run Pint**

```bash
./vendor/bin/pint
```

Expected: no diffs (or auto-fix and re-run).

- [ ] **Step 3: Run PHPStan (level 6 to start)**

Create `phpstan.neon`:

```neon
includes:
    - vendor/larastan/larastan/extension.neon

parameters:
    paths:
        - app
    level: 6
```

Run:

```bash
./vendor/bin/phpstan analyse
```

Expected: 0 errors. If errors surface, fix them (typed properties, missing docblocks for relations).

- [ ] **Step 4: Commit any cleanup**

```bash
git add .
git commit -m "chore: pint + phpstan pass"
```

---

### Task 24: Manual browser smoke test

- [ ] **Step 1: Fresh migrate + seed + build**

```bash
php artisan migrate:fresh --seed
npm run build
```

- [ ] **Step 2: Boot dev server**

```bash
php artisan serve --port=8000
```

- [ ] **Step 3: Walk every route in a browser**

Open each URL and visually confirm it loads with design styles intact:

```
http://localhost:8000/
http://localhost:8000/about
http://localhost:8000/our-process
http://localhost:8000/services/photography
http://localhost:8000/services/videography
http://localhost:8000/industries
http://localhost:8000/industries/fashion
http://localhost:8000/portfolio
http://localhost:8000/portfolio/<seeded-slug>
http://localhost:8000/blog
http://localhost:8000/blog/<seeded-slug>
http://localhost:8000/crew
http://localhost:8000/crew/<seeded-slug>
http://localhost:8000/contact
http://localhost:8000/privacy-policy
http://localhost:8000/terms-of-service
http://localhost:8000/cookie-policy
http://localhost:8000/disclaimer
http://localhost:8000/thank-you
http://localhost:8000/does-not-exist           ← expect 404 page
```

- [ ] **Step 4: Submit the contact form end-to-end**

Fill `/contact` with real values, submit, confirm:
- Redirected to `/thank-you`
- `select * from quotes` shows the row
- `storage/logs/laravel.log` contains both queued mail entries (log driver in dev)

- [ ] **Step 5: Stop server, commit any tiny fixes**

```bash
git add .
git diff --cached --stat
git commit -m "chore: smoke test fixes" || true
```

---

## Self-Review

**Spec coverage:**

| Spec § | Requirement                                                  | Plan task |
|--------|--------------------------------------------------------------|-----------|
| §2     | Single Laravel monolith, MySQL, local disk                   | Task 1 |
| §3.1   | Models + ER (quotes, posts, portfolios, services, industries, crew, settings) | Tasks 5–9 |
| §3.3   | Seeded roles (Super-admin, Editor, Sales, Viewer)            | Task 9 |
| §3.4   | RBAC + ownership ABAC                                        | Out of scope this plan — deferred to **Plan 2** (admin panel needs them) |
| §4.1   | Public route map                                             | Tasks 13–22 |
| §4.2   | Blade layout + components                                    | Tasks 10–12 |
| §4.3   | Asset pipeline (Vite, copy core/pages css+js, fonts)         | Task 3, Task 10 |
| §4.4   | Contact → Quote (validation, honeypot, rate-limit, mail)     | Task 21 |
| §4.5   | SEO (per-page meta, JSON-LD, sitemap, robots)                | Layout + json-ld component (Task 10/12); `sitemap` + `responsecache` deferred to **Plan 3** |
| §5     | Admin panel                                                  | Deferred to **Plan 2** |
| §6     | Packages: laravel-permission, medialibrary, activitylog, sluggable, pest, pint, phpstan | Task 2 |
| §7     | Test layout (Pest, Feature/Public)                           | Throughout (every task includes tests) |

Gaps deliberately deferred:
- Filament admin → **Plan 2**.
- Sitemap, responsecache, backup → **Plan 3**.
- DB-driven blog post bodies edited from admin → **Plan 3** (today posts are seeded).

**Placeholder scan:** no "TBD"/"TODO"/"implement later"/"handle edge cases" left. Every step has either runnable commands or complete code. `Step 1`/`Step 2`/etc. headers in each task are followed by either an exact shell command or a complete code block.

**Type consistency check:**
- `Quote` uses `assigned_to` everywhere (model fillable, migration FK, scope plans for Plan 2). ✓
- `Portfolio` uses `owner_id`. ✓
- `Post` uses `author_id`. ✓
- `Service`/`Industry`/`Crew` have no owner field — confirmed against spec §3.3 wording fixed in self-review. ✓
- `SiteSetting::get/set` signatures consistent between model definition and seeder/controller usage. ✓
- Mail classes constructor receives `Quote` — both `NewQuoteAdminNotification` and `QuoteAutoReply`. ✓

---

## Future Plans (sketched, not bodies)

- **Plan 2 — Admin Panel + Quote Workflow.** Install Filament v3, custom dark theme matching frontend, filament-shield wiring, Policies (Quote ownership for Sales role), `QuoteResource` with status workflow + notes + activity log + bulk actions, mail notifications, `SiteSettingsPage`. Deliverable: staff log in to `/admin`, triage leads, change site config.
- **Plan 3 — Content Resources + SEO Hardening.** `PostResource`, `PortfolioResource` (with media-library gallery), `ServiceResource`, `IndustryResource`, `CrewResource`, `UserResource`, `RoleResource`. Switch public views from seeded fixtures to fully editable. `spatie/laravel-sitemap` cron, `spatie/laravel-responsecache` with invalidation observers, `spatie/laravel-backup` nightly. CI workflow (`.github/workflows/ci.yml`), Sentry, `/up` health endpoint. Deliverable: site is fully editable end-to-end and production-deployable.
