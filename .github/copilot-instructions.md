## Models
- Modes that implement status must use spatie model-status package and implemnt teh HasStatuses trait.
- Models that has sortable behaviour must implement spatie eloquent-sortable package and implement the Sortable interface and use the Sortable trait.
- Models that have currency or money properties must implement ariaieboy/filament-currency package and the correct schema property. https://github.com/ariaieboy/filament-currency

## Business Logic
- Business logic should be implemented using lorisleiva/laravel-actions package. Each action should be placed in the App\Actions namespace and be as granular as possible. Actions can be grouped into sub-namespaces as needed.
- Actions should be invokable classes that encapsulate a single piece of business logic. They should be reusable and testable.
- Actions can be used in controllers, models, or other parts of the application to perform specific tasks.
