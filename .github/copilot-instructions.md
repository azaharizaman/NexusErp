## Models
- Modes that implement status must use spatie model-status package and implemnt teh HasStatuses trait.
- Models that has sortable behaviour must implement spatie eloquent-sortable package and implement the Sortable interface and use the Sortable trait.
- Models that have currency or money properties must implement ariaieboy/filament-currency package and the correct schema property. https://github.com/ariaieboy/filament-currency
