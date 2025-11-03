## General
- Before you start working on a tast, plan out what your approach will be in step by step manner.
- Always refer to the existing codebase and follow the established patterns and conventions.
- Write clean, maintainable, and well-documented code.
- Ensure that you have a good understanding of FilamentPHP version 4, Laravel 12, and any other relevant technologies used in the project.
- Write unit and feature tests for any new functionality you implement.
- Use the existing test suites to verify that your changes do not introduce any regressions.
- Follow the project's coding standards and best practices.
- Communicate with the team if you encounter any challenges or need clarification on requirements.
- Always use context7 to pull up the latest documentation on filamentPHP version 4 or specifically 4.1 before starting any changes to the code.
- After completing task, please update the ARCHITECTURAL_DECISIONS.md file with any new architectural decisions made during the implementation.
- Document any changes made to the codebase, including the rationale behind the changes and any relevant context. This documentation should be clear and concise to help future developers understand the reasoning behind the changes.
- Update the PROGRESS_CHECKLIST.md file to reflect the completion of the task, including any relevant details about the implementation that was suggested or can be hold for future improvements. Do not remove any existing items from the checklist; only add new items as needed and mark those that are completed.

## Models
- Models that implement status must use spatie model-status package and implement the HasStatuses trait.
- Models that has sortable behaviour must implement spatie eloquent-sortable package and implement the Sortable interface and use the Sortable trait.
- Models that have currency or money properties must implement ariaieboy/filament-currency package and the correct schema property. https://github.com/ariaieboy/filament-currency

## Business Logic
- Business logic should be implemented using lorisleiva/laravel-actions package. Each action should be placed in the App\Actions namespace and be as granular as possible. Actions can be grouped into sub-namespaces as needed.
- Actions should be invokable classes that encapsulate a single piece of business logic. They should be reusable and testable.
- Actions can be used in controllers, models, or other parts of the application to perform specific tasks.
