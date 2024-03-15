# **Laravel ACalendar Package**

The Laravel ACalendar package is designed to enrich Laravel applications with advanced event management capabilities. It allows developers to seamlessly integrate event functionalities into Eloquent models, manage event occurrences, and handle repeating events with ease. This guide outlines the package's main features, installation process, and usage with detailed examples.

## **Features and Main Concepts**

- **Flexible Event Management**: Create, update, and delete events directly associated with Eloquent models.
- **Support for Various Event Types**: Handles different types of events, including single, all-day, ranged dates, and timed events.
- **Repeating Events**: Comprehensive support for repeating events with customizable frequencies.
- **Eloquent Model Integration**: Easy integration with any Eloquent model using a trait and interface.
- **Dynamic Event Instances Generation**: Automatically handles the generation of event instances for repeating events within specified date ranges.
- **Custom Event Collection Method**: Provides a **`byDay()`** method for grouping event instances, facilitating calendar views or daily summaries.

## **Installation**

1. Install the package via Composer:

```bash
composer require aurorawebsoftware/acalendar

```

1. Publish the configuration and migration files:

```bash
php artisan vendor:publish --provider="AuroraWebSoftware\ACalendar\ACalendarServiceProvider"

```

1. Execute the migrations:

```bash
php artisan migrate

```

## **Enums**

### **Type Enum**

- **`DATE_ALL_DAY`**: Events occurring throughout the day.
- **`DATE_POINT`**: Events assigned to a specific date.
- **`DATETIME_POINT`**: Events assigned to a specific datetime.
- **`DATE_RANGE`**: Events spanning across multiple dates.
- **`DATETIME_RANGE`**: Events with a specific start and end datetime.

### **RepeatFrequency Enum**

- **`DAY`**: Event repeats daily.
- **`WEEK`**: Event repeats weekly.
- **`MONTH`**: Event repeats monthly.
- **`YEAR`**: Event repeats yearly.

## **Integration with Models**

Implement the **`EventableModelContract`** and use the **`HasEvents`** trait within your model:

```php
namespace App\Models;

use AuroraWebSoftware\ACalendar\Contracts\EventableModelContract;
use AuroraWebSoftware\ACalendar\Traits\HasEvents;
use Illuminate\Database\Eloquent\Model;

class Task extends Model implements EventableModelContract
{
    use HasEvents;

    protected $fillable = ['name'];

    public static function getModelType(): string
    {
        return self::class;
    }

    public function getModelId(): int
    {
        return $this->id;
    }

    public function getEventTitle(): ?string
    {
        return $this->name;
    }
}

```



## **Usage Examples**

### **Creating Events**

```php
$task = Task::find(1);
$task->updateOrCreateEvent(
    key: 'deadline',
    type: Type::DATE_POINT,
    start: Carbon::tomorrow(),
    title: 'Preparing SRS Docs'
);

$task->updateOrCreateEvent(
    key: 'deadline',
    type: Type::DATE_POINT,
    start: Carbon::tomorrow(),
    title: 'Preparing SRS Docs'
);

```

> Only one event can be created for a model with a key

### **Retrieving Event Instances**

- Dynamic method on an instance:

```php
$events = $task->eventInstances('deadline', Carbon::now(), Carbon::now()->addMonth(1));
```

- Static method on the model class:

```php
$events = Task::allEventInstances('deadline', Carbon::now(), Carbon::now()->addMonth(1));
```

### **Handling Repeating Events**

```php
$meeting = Meeting::find(1);
$meeting->updateOrCreateEvent(
    key: 'Weekly Review',
    type: Type::DATETIME_POINT,
    start: Carbon::parse('next monday 10:00'),
    repeatFrequency: RepeatFrequency::WEEK,
    repeatPeriod: 1,
    title: 'Weekly Review Meeting'
);

```

### **Using `byDay` Method**

The byDay() method in the Laravel ACalendar package groups event instances by their occurrence date, returning a collection where each key is a date and the value is a collection of events happening on that date. This method simplifies creating calendar views or daily schedules by organizing events in a date-indexed format, making it straightforward to display what events are happening on each day.

-  Facilitates the development of calendar interfaces by categorizing events by day.

```php
$eventInstances = $meeting->eventInstances(null, Carbon::now(), Carbon::now()->addWeeks(4));
$byDay = $eventInstances->byDay();

foreach ($byDay as $date => $events) {
    echo "Date: $date\n";
    foreach ($events as $event) {
        echo "- {$event->title} at {$event->start->format('H:i')}\n";
    }
}

```






## **Scenario Setup**

Assuming we have three models - **`Conference`**, **`Webinar`**, and **`Exhibition`**, each integrated with the ACalendar package as shown in previous examples. These models will demonstrate different event types, such as **`DATE_ALL_DAY`**, **`DATE_RANGE`**, and **`DATETIME_RANGE`**.

### **Conference: All-Day Event**

Conferences often last the entire day. Here's how you might set up an all-day event for a conference:

```php
$conference = Conference::create(['name' => 'Tech Innovators Conference', 'description' => 'A gathering of technology innovators.']);

$conference->updateOrCreateEvent(
    key: 'tech_innovators_2024',
    type: Type::DATE_ALL_DAY,
    start: Carbon::parse('2024-09-10'),
    title: 'Tech Innovators Conference - All Day'
);

```

### **Webinar: Date Range Event**

Webinars can span multiple days. This example demonstrates creating an event that covers a range of dates:

```php
$webinar = Webinar::create(['title' => 'Digital Marketing 101', 'host' => 'Marketing Gurus']);

$webinar->updateOrCreateEvent(
    key: 'digital_marketing_101',
    type: Type::DATE_RANGE,
    start: Carbon::parse('2024-10-05'),
    end: Carbon::parse('2024-10-07'),
    title: 'Digital Marketing 101 Webinar'
);

```

### **Exhibition: DateTime Range Event**

Exhibitions may have specific start and end times. Here's how you'd set up an event with a datetime range:

```php
$exhibition = Exhibition::create(['name' => 'Artists of the 21st Century', 'location' => 'City Art Gallery']);

$exhibition->updateOrCreateEvent(
    key: '21st_century_artists',
    type: Type::DATETIME_RANGE,
    start: Carbon::parse('2024-11-20 09:00'),
    end: Carbon::parse('2024-11-20 17:00'),
    title: 'Artists of the 21st Century Exhibition'
);

```

## **Querying and Displaying Event Instances**

### **Displaying Upcoming Conferences**

Retrieve and display all upcoming conferences for the next year:

```php
phpCopy code
$upcomingConferences = Conference::allEventInstances(
    null,
    Carbon::now(),
    Carbon::now()->addYear(1)
);

foreach ($upcomingConferences as $event) {
    echo "Conference: {$event->title} on {$event->start->toDateString()}\n";
}

```

### **Webinar Schedule for the Next Month**

Generate a schedule of all webinars happening in the next month, grouped by day:

```php
phpCopy code
$nextMonthWebinars = Webinar::allEventInstances(
    null,
    Carbon::now()->addMonth(),
    Carbon::now()->addMonths(2)
)->byDay();

foreach ($nextMonthWebinars as $date => $webinars) {
    echo "Date: $date\n";
    foreach ($webinars as $webinar) {
        echo "- Webinar: {$webinar->title} from {$webinar->start->toDateString()} to {$webinar->end->toDateString()}\n";
    }
}

```

### **Exhibition Hours**

For exhibitions, it might be useful to know the exact opening and closing times:

```php
phpCopy code
$exhibitionDetails = Exhibition::allEventInstances('21st_century_artists', Carbon::now(), Carbon::now()->addMonth(1));

foreach ($exhibitionDetails as $detail) {
    echo "Exhibition: {$detail->title}, Start: {$detail->start->toDateTimeString()}, End: {$detail->end->toDateTimeString()}\n";
}

```

These examples illustrate just a few of the many possibilities enabled by the Laravel ACalendar package for managing events. By leveraging different event types and repeat frequencies, developers can tailor the package to meet a wide array of event management needs within their Laravel applications.






This Laravel ACalendar package guide aims to provide a solid foundation for integrating and utilizing event management within your Laravel applications. By following the installation instructions and exploring the comprehensive examples, you can leverage the package's functionalities to enhance your projects with sophisticated event handling capabilities.