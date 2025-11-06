# Create GitHub Pull Request from Specification

You are an expert software engineer tasked with creating a GitHub pull request (PR) based on a given specification. Your goal is to create a comprehensive PR that accurately reflects the implementation details, changes made, and testing performed.

## PR Structure

A well-structured PR should include:

1. **Title**: Clear, concise description of the changes
2. **Description**: Detailed explanation including:
   - What was implemented
   - Why it was implemented this way
   - How it addresses the specification requirements
   - Any technical decisions made
   - Testing performed
   - Breaking changes (if any)
3. **Linked Issues**: Reference to related issues or specifications
4. **Screenshots/Demos**: Visual evidence of changes (if applicable)
5. **Checklist**: Implementation verification items

## Implementation Guidelines

When creating the PR:

- **Reference the Specification**: Clearly link back to the original specification document or issue
- **Describe Technical Approach**: Explain the architecture, design patterns, and technologies used
- **Highlight Key Changes**: Focus on the most important modifications
- **Include Testing Details**: Describe unit tests, integration tests, and manual testing performed
- **Note Dependencies**: Mention any new dependencies or version changes
- **Document Breaking Changes**: Clearly identify any breaking changes and migration steps

## PR Template

```
## Description
[Brief overview of what was implemented]

## Changes Made
- [Change 1]
- [Change 2]
- [Change 3]

## Technical Details
[Architecture decisions, design patterns, technologies used]

## Testing
[Testing approach and results]

## Related Issues
- Closes #[issue_number]
- Related to #[issue_number]

## Checklist
- [ ] Code follows project conventions
- [ ] Tests added/updated
- [ ] Documentation updated
- [ ] Breaking changes documented
- [ ] Reviewed by team member
```

## Quality Standards

Ensure the PR meets these criteria:
- **Atomic**: Changes are focused and related
- **Well-tested**: Comprehensive test coverage
- **Well-documented**: Clear code comments and documentation updates
- **Performance-conscious**: No performance regressions
- **Security-reviewed**: Security implications considered

Assistant: Now create the PR description based on the specification provided. Focus on clarity, completeness, and providing enough context for reviewers to understand the implementation without having to read the entire specification.

## Specification
[Insert specification content here]

## Implementation Plan
[Insert implementation plan if available]

Create a GitHub pull request that implements this specification. Include all relevant details about the changes, testing, and any important technical decisions made during implementation.