# Analysis HMS - Task Templates

## Task Template Overview

This document provides reusable task templates for common development tasks.

---

## Template 1: Bug Fix

### Bug Fix Template

```markdown
## Bug Fix: [Bug Title]

### Bug Description
[Clear description of the bug]

### Steps to Reproduce
1. [Step 1]
2. [Step 2]
3. [Step 3]

### Expected Behavior
[What should happen]

### Actual Behavior
[What actually happens]

### Root Cause
[Analysis of why the bug occurred]

### Solution
[How the bug was fixed]

### Files Modified
- [File 1]
- [File 2]

### Testing
- [ ] Unit tests added
- [ ] Feature tests added
- [ ] Manual testing completed

### Verification
- [ ] Bug is fixed
- [ ] No regression
- [ ] Tests pass

### Related Issues
- [Issue #1]
- [Issue #2]
```

---

## Template 2: New Feature

### New Feature Template

```markdown
## Feature: [Feature Title]

### Requirements
[Detailed requirements]

### User Stories
- As a [user], I want [feature] so that [benefit]

### Design
[Design details]

### Implementation Plan
1. [Step 1]
2. [Step 2]
3. [Step 3]

### Database Changes
- New tables: [tables]
- Modified tables: [tables]
- New columns: [columns]

### API Changes
- New endpoints: [endpoints]
- Modified endpoints: [endpoints]

### UI Changes
- New views: [views]
- Modified views: [views]

### Files to Create
- [File 1]
- [File 2]

### Files to Modify
- [File 1]
- [File 2]

### Testing
- [ ] Unit tests
- [ ] Feature tests
- [ ] Integration tests
- [ ] UI testing

### Documentation
- [ ] Architecture updated
- [ ] API docs updated
- [ ] Business rules updated

### Deployment
- [ ] Staging tested
- [ ] Production deployed
- [ ] Verified in production
```

---

## Template 3: UI Update

### UI Update Template

```markdown
## UI Update: [Update Title]

### Current UI
[Screenshot or description of current UI]

### Proposed UI
[Screenshot or description of proposed UI]

### Changes
- [Change 1]
- [Change 2]
- [Change 3]

### Components Affected
- [Component 1]
- [Component 2]

### Responsive Design
- [ ] Desktop
- [ ] Tablet
- [ ] Mobile

### Accessibility
- [ ] Keyboard navigation
- [ ] Screen reader
- [ ] Color contrast

### Browser Testing
- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge

### Files to Modify
- [File 1]
- [File 2]

### Testing
- [ ] Visual testing
- [ ] Responsive testing
- [ ] Accessibility testing
```

---

## Template 4: Performance Improvement

### Performance Improvement Template

```markdown
## Performance: [Improvement Title]

### Current Performance
[Current metrics]

### Target Performance
[Target metrics]

### Analysis
[Performance analysis]

### Solution
[Proposed solution]

### Implementation
1. [Step 1]
2. [Step 2]
3. [Step 3]

### Files to Modify
- [File 1]
- [File 2]

### Testing
- [ ] Performance testing
- [ ] Load testing
- [ ] Stress testing

### Results
[Performance improvements achieved]

### Monitoring
[How to monitor performance]
```

---

## Template 5: Database Change

### Database Change Template

```markdown
## Database Change: [Change Title]

### Current Schema
[Current database structure]

### Proposed Schema
[Proposed database structure]

### Migration
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Migration code
    }

    public function down()
    {
        // Rollback code
    }
};
```

### Data Migration
[Data migration strategy]

### Rollback Strategy
[How to rollback]

### Testing
- [ ] Migration tested
- [ ] Rollback tested
- [ ] Data integrity verified

### Impact
[Impact on existing code]

### Files to Modify
- [File 1]
- [File 2]
```

---

## Template 6: API Development

### API Development Template

```markdown
## API: [Endpoint Title]

### Endpoint
`[METHOD] /api/[endpoint]`

### Description
[What the endpoint does]

### Request
```json
{
    "field": "value"
}
```

### Response
```json
{
    "field": "value"
}
```

### Authentication
[How to authenticate]

### Rate Limiting
[Rate limit rules]

### Validation
[Validation rules]

### Error Responses
```json
{
    "error": "message"
}
```

### Implementation
1. [Step 1]
2. [Step 2]
3. [Step 3]

### Files to Create
- [File 1]
- [File 2]

### Testing
- [ ] Unit tests
- [ ] Feature tests
- [ ] API tests

### Documentation
- [ ] API docs updated
- [ ] Postman collection updated
```

---

## Template 7: Testing

### Testing Template

```markdown
## Testing: [Test Title]

### Test Type
- [ ] Unit Test
- [ ] Feature Test
- [ ] Integration Test
- [ ] E2E Test

### Test Cases
1. [Test case 1]
2. [Test case 2]
3. [Test case 3]

### Test Data
[Test data requirements]

### Expected Results
[Expected test results]

### Files to Create/Modify
- [File 1]
- [File 2]

### Test Execution
```bash
# Run tests
php artisan test --filter=[TestName]
```

### Coverage
[Target coverage]
```

---

## Template 8: Documentation

### Documentation Template

```markdown
## Documentation: [Documentation Title]

### Document Type
- [ ] Architecture
- [ ] API
- [ ] Business Rules
- [ ] Developer Guide
- [ ] User Guide

### Content
[Documentation content]

### Files to Create/Modify
- [File 1]
- [File 2]

### Review
- [ ] Technical review
- [ ] Accuracy check
- [ ] Completeness check
```

---

## Template 9: Security Review

### Security Review Template

```markdown
## Security Review: [Review Title]

### Scope
[What is being reviewed]

### OWASP Checklist
- [ ] A01: Broken Access Control
- [ ] A02: Cryptographic Failures
- [ ] A03: Injection
- [ ] A04: Insecure Design
- [ ] A05: Security Misconfiguration
- [ ] A06: Vulnerable Components
- [ ] A07: Auth Failures
- [ ] A08: Data Integrity Failures
- [ ] A09: Logging Failures
- [ ] A10: SSRF

### Findings
[Security findings]

### Recommendations
[Security recommendations]

### Files to Modify
- [File 1]
- [File 2]

### Testing
- [ ] Penetration testing
- [ ] Vulnerability scanning
- [ ] Security testing
```

---

## Template 10: Refactoring

### Refactoring Template

```markdown
## Refactoring: [Refactoring Title]

### Current Code
[Current code structure]

### Proposed Code
[Proposed code structure]

### Reasons
[Why refactoring is needed]

### Benefits
[Benefits of refactoring]

### Implementation
1. [Step 1]
2. [Step 2]
3. [Step 3]

### Files to Modify
- [File 1]
- [File 2]

### Testing
- [ ] Unit tests
- [ ] Feature tests
- [ ] Regression tests

### Verification
- [ ] Code quality improved
- [ ] No functionality change
- [ ] Tests pass
```

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
