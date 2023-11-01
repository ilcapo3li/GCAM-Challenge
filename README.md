
# GCAM Coding Challenge

It's amazing task to show some examples of my experiance through the challenge, i tried different strategies to deliver this task 
such docker, meillisearch "cache-products" layers, Redis "Queue Service", Mailpit local mailbox
## Overview

This challenge's stratigies of Laravel/Inertia development:

* PHP's OOP implementation (design patterns)
  using repositories, observers, services.

* Pgsql (Docker image DB)

* RESTful API integration (Inertia SPA)

* Efficient workload processing (Seeders - Factories).

## Project Description

Project Used laravel sail to initiate dependencies very fast and to be unfied across all devices which need to test the code.

### 1. Setup the Poject!

* First you need to make download the repository to your local machine
* Install Docker, Dokcer-Compose.
* Install dependencies you need to run this command

```sh
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

* now you need to intiate project

```sh
./vendor/bin/sail up -d                         //initialize all services from docker compose file 
./vendor/bin/sail php artisan migrate --seed    //migrate DB tables and Seed Factories Faker Data
```

```php
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->admin();
        User::factory(20)
            ->has(Board::factory()->count(10)
                ->has(BoardItem::factory()->todo()->count(15)
                ->state(function (array $attributes, Board $board) {
                    return [
                        'created_by' => $board->created_by,
                    ];
                }), 'boardItems')
                ->has(BoardItem::factory()->completed()->count(20)
                ->state(function (array $attributes, Board $board) {
                    return [
                        'created_by' => $board->created_by,
                    ];
                }), 'boardItems'),
            'boards')->create();

    }
```

### 2. Services
*PGsql Database
*Redis
*Meillisearch
*Adminer
*SailApp
*Mailpit

### 3. Challange TODO ARCH

* The Problem Here we need to create Scalable TODO list, we can build on this arch any time:
 1-  Board.
 2-  BoardItems.
 3-  AssigneeBoardItem, "AssigneesToSameIssue"
 4-  InviteUser "InviteNEWUserToBoard"
 5-  BoardUser "InviteSystemUserToBoard"
 6-  User

* BoardItems Can Scale to be "Task/Subtask/Epic/Story"

```php
namespace App\Enums;

enum TaskType: int
{
    case TASK = 1;
    case STORY = 2;
    case EPIC = 3;
    case SUB_TASK = 4;


    public function name(): string
    {
        return match ($this) {
            static::TASK => ucfirst(__('task')),
            static::STORY  => ucfirst(__('story')),
            static::EPIC => ucfirst(__('epic')),
            static::SUB_TASK  => ucwords(__('sub task')),
        };
    }
}
```

### 4. Challange TODO EVENTS

* Also We Can Observe On BoardItems Status Event Changes Here we can broadcast/email/log what we need

```php
namespace App\Observers;

use App\Models\BoardItem;
use Illuminate\Support\Facades\Log;

class BoardItemObserver
{
    /**
     * Handle the BoardItem "created" event.
     *
     * @param \App\Models\BoardItem $boardItem
     * @return void
     */
    public function created(BoardItem $boardItem)
    {
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/created.log'),
        ])->info(__('Issue :code created', ['code' => $boardItem->code]));
    }

    /**
     * Handle the BoardItem "updated" event.
     *
     * @param \App\Models\BoardItem $boardItem
     * @return void
     */
    public function updated(BoardItem $boardItem)
    {
        if ($boardItem->wasChanged('status_id')) {
            $message = match ($boardItem->status->title) {
                'IN_PROCESS' => __('Issue :code updated status to :to', ['code' => $boardItem->code, 'to' => $boardItem->status->title]),
                'COMPLETED' => __('Issue :code updated status to :to', ['code' => $boardItem->code, 'to' => $boardItem->status->title]),
            };
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/updated.log'),
            ])->info($message);
        }
    }
}
```

### 5. Issues

* Divide and Conquer Scope to Deliver Well.

* https://github.com/ilcapo3li/GCAM-Challenge/issues/1

* https://github.com/ilcapo3li/GCAM-Challenge/issues/2

* https://github.com/ilcapo3li/GCAM-Challenge/issues/4

* https://github.com/ilcapo3li/GCAM-Challenge/issues/6

### 6. Pull Requests

* This will Help On Coding Review and Documentation

* https://github.com/ilcapo3li/GCAM-Challenge/pull/3

* https://github.com/ilcapo3li/GCAM-Challenge/pull/5

* https://github.com/ilcapo3li/GCAM-Challenge/pull/7


### https://github.com/ilcapo3li/GCAM-Challenge/issues

***All the best***
