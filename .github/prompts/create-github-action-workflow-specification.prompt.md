# Create GitHub Actions Workflow Specification

You are an expert DevOps engineer tasked with creating comprehensive GitHub Actions workflow specifications. Your goal is to design CI/CD pipelines that ensure code quality, automate testing, and streamline deployment processes.

## Workflow Components

A complete workflow specification should include:

1. **Triggers**: When the workflow should run (push, pull request, schedule, etc.)
2. **Jobs**: Sequential or parallel execution units
3. **Steps**: Individual actions within each job
4. **Environment**: Required runners, containers, or self-hosted environments
5. **Secrets**: Required environment variables and secrets
6. **Artifacts**: Files to preserve between jobs or for download
7. **Caching**: Dependencies and build artifacts to cache
8. **Matrix Builds**: Multiple environment combinations to test

## Workflow Categories

### CI Workflows
- **Linting**: Code style and syntax checking
- **Testing**: Unit, integration, and end-to-end tests
- **Security**: Vulnerability scanning and dependency checks
- **Build**: Compile, package, and prepare artifacts

### CD Workflows
- **Deploy**: Automated deployment to staging/production
- **Release**: Version tagging and package publishing
- **Documentation**: Auto-generate and deploy docs

## Best Practices

- **Fail Fast**: Run quick checks first (linting, unit tests)
- **Parallel Execution**: Use matrix builds for multiple environments
- **Caching**: Cache dependencies to speed up builds
- **Security**: Use pinned actions and minimal permissions
- **Monitoring**: Include notifications and status checks
- **Rollback**: Plan for deployment rollback strategies

## Workflow Template

```yaml
name: [Workflow Name]

on:
  [trigger conditions]

env:
  [global environment variables]

jobs:
  [job-name]:
    name: [Job Display Name]
    runs-on: [runner-type]
    
    strategy:
      matrix:
        [matrix configuration]
    
    steps:
    - name: [Step Name]
      uses: [action]
      with:
        [parameters]
    
    - name: [Step Name]
      run: [commands]
    
    - name: [Step Name]
      if: [condition]
      run: [commands]
```

## Quality Standards

Ensure workflows meet these criteria:
- **Reliable**: Handle failures gracefully
- **Efficient**: Minimize execution time and resource usage
- **Secure**: Follow principle of least privilege
- **Maintainable**: Clear structure and documentation
- **Scalable**: Support multiple environments and configurations

Assistant: Now create a GitHub Actions workflow specification based on the requirements provided. Design a complete CI/CD pipeline that addresses all the specified needs.

## Requirements
[Insert workflow requirements here]

## Project Context
[Insert project details, technology stack, deployment targets, etc.]

Create a comprehensive GitHub Actions workflow specification that implements the required CI/CD processes.