# Analysis HMS - AI Memory Management

## ⚠️ MASTER PROMPT REFERENCE

**This memory system is guided by the Master Enterprise AI Software Engineering Prompt.**

Read `.ai/MASTER_PROMPT.md` for complete guidelines on memory management, graph memory, and knowledge retention.

---

## Memory Overview

This document defines how AI agents maintain long-term memory for the Analysis HMS project. Memory enables AI to remember context, decisions, patterns, and knowledge across sessions.

**Memory is MANDATORY. Every code change MUST trigger memory updates.**

---

## 📊 Latest Analysis Snapshot (2026-08-16 — VERIFIED)

> ⚠️ The 2026-08-07 snapshot below describes work that is **not in the repository** (repo has a single commit `67e9744` "Initial upload"). Verified state as of 2026-08-16:
> - **Stored XSS fixed** (BUG-022): 3 ticket views now `{{ nl2br(e($ticket->problem)) }}`
> - **ToolsController SQL verified safe** (BUG-023): whitelist + DB-introspection validation + auth gate (superadmin/property-20)
> - **`formatCurrency` helper added** (BUG-027) — it was documented but missing from code; tests were failing (7 failed / 20 passed) → now **27 passed (33 assertions)**
> - **Knowledge base built**: MASTER_PROJECT_MAP, MODULE_STATUS, BUG_REGISTER, MISSING_FEATURES/REPORTS/LOGIC, SECURITY_AUDIT, PERFORMANCE_AUDIT, DATABASE_MAP, ROUTE_MAP, UI_MAP, LEGACY_TO_LARAVEL_MAP, CHANGELOG_AI, NEXT_TASK, COMPLETED_TASKS
> - **Work queue**: `.ai/NEXT_TASK.md` — next: advance/folio reconciliation report (P0)

Full report: `.ai/ANALYSIS_REPORT.md` (2026-08-07, partially outdated — cross-check with `.ai/MASTER_PROJECT_MAP.md`) • Research log: `.ai/RESEARCH.md` • Upgrade plan: `.ai/UPGRADE_PLAN.md`

**Critical knowledge to remember (2026-08-07 snapshot — re-verify before relying):**
- **PHP 8.2.33 / Laravel 10.50.2 / MySQL** (db_analysishms) / Composer 2.10.2
- **NO Git repository** — ✅ **RESOLVED 2026-08-07**: git initialized (branch `main`, baseline commit `809669c`); DB backup pending (MySQL server stopped — start via XAMPP Control Panel)
- **WebSockets: beyondcode → Laravel Reverb ^1.0** (Phase 1, 2026-08-07) — `BROADCAST_DRIVER=reverb`, server 127.0.0.1:8080, events unchanged (Pusher protocol), dispatch sites try/catch-wrapped. Legacy files deleted. Echo client still commented out; **REVERB_APP_* keys currently EMPTY in dev — set real values before enabling any frontend Echo client**
- **Audit: 29 → 3 advisories** (2026-08-07) — patched dompdf 3.1.6, guzzle 7.15.3, psr7 2.13.0, commonmark 2.9.0, phpspreadsheet 5.9.0; only EOL laravel/framework remains
- **APP_DEBUG=true, APP_ENV=local** — disable before production (BUG-024)
- **Stored XSS** in 3 ticket views via `{!! $ticket->problem !!}` (BUG-022)
- **All models use $fillable** — mass assignment safe (verified)
- **No eager loading anywhere** — N+1 risk on list/report pages (BUG-025)
- **God controllers**: CompanyController 22K lines, PrintController 6K, InventoryController 5.9K, Reporting 5.4K
- **Only 2 cache usages** in the app; queues on `sync`; cache/session on `file` (BUG-026)
- **162 models, 549 blade views, 412 migrations, 19 middleware, 12 route files, 13 API routes**
- **26 tests passing** (HelpersTest + RouteTest, SQLite in-memory)
- **Helpers pattern**: all wrapped in `function_exists()` guards
- **Events**: only `SaleBillPrintEvent` + `PrintEvent` (websocket printing)
- **No Jobs/Observers/Policies/Notifications** — logic is controller-inline; `AccountPosting` service isolates accounting

**2026 Research highlights (`.ai/RESEARCH.md`):**
- 🔴 **Laravel 10 is EOL** (security ended early 2025) → upgrade to Laravel 12 planned (Decision 12)
- 🔴 **PHP 8.2 EOL Dec 31, 2026** → upgrade to 8.3/8.4 planned (Decision 14)
- 🟠 **yajra datatables ^10.11 inside AIKIDO-2025-10705 RCE range** → upgrade to 12.6.0+/13
- 🟠 **phpspreadsheet** high CVE churn 2025-26 → pin latest + `composer audit`
- 🟠 **beyondcode/laravel-websockets abandoned** → migrate to Laravel Reverb (Decision 13)
- 🟢 OPcache/JIT config, eager loading, Redis are top performance wins

---

## Memory Categories

### 1. Architecture Memory

#### Stored Information
- System architecture decisions
- Design patterns used
- Component relationships
- Technology choices
- Integration points

#### Memory Structure
```markdown
## Architecture Decision
- **Date**: YYYY-MM-DD
- **Decision**: [What was decided]
- **Reason**: [Why it was decided]
- **Alternatives**: [What else was considered]
- **Impact**: [What was affected]
- **Status**: [Active/Superseded]
```

#### Update Triggers
- New architectural decision
- Technology change
- Pattern adoption
- Integration addition

---

### 2. Coding Style Memory

#### Stored Information
- Naming conventions
- Code structure patterns
- Comment styles
- Error handling approaches
- Testing patterns

#### Memory Structure
```markdown
## Coding Convention
- **Category**: [Naming/Structure/Comments/etc.]
- **Pattern**: [The convention]
- **Example**: [Code example]
- **Rationale**: [Why this convention]
- **Status**: [Active/Deprecated]
```

#### Update Triggers
- New coding standard
- Pattern change
- Convention update
- Best practice adoption

---

### 3. Business Rules Memory

#### Stored Information
- Hotel workflow rules
- Reservation logic
- Check-in/check-out rules
- Billing rules
- GST compliance rules
- Night audit rules

#### Memory Structure
```markdown
## Business Rule
- **Module**: [Which module]
- **Rule**: [The rule]
- **Implementation**: [How it's implemented]
- **Edge Cases**: [Special cases]
- **Status**: [Active/Modified]
```

#### Update Triggers
- New business requirement
- Rule modification
- Compliance change
- Edge case discovery

---

### 4. Database Knowledge Memory

#### Stored Information
- Table structures
- Relationships
- Indexes
- Stored procedures
- Triggers
- Performance patterns

#### Memory Structure
```markdown
## Database Knowledge
- **Table**: [Table name]
- **Knowledge**: [What was learned]
- **Performance**: [Performance notes]
- **Relationships**: [Related tables]
- **Status**: [Current/Outdated]
```

#### Update Triggers
- Schema change
- Performance issue
- New relationship
- Index optimization

---

### 5. API Knowledge Memory

#### Stored Information
- API endpoints
- Request/response formats
- Authentication methods
- Rate limiting rules
- Error handling patterns

#### Memory Structure
```markdown
## API Knowledge
- **Endpoint**: [API endpoint]
- **Knowledge**: [What was learned]
- **Usage**: [How it's used]
- **Limitations**: [Known limitations]
- **Status**: [Current/Deprecated]
```

#### Update Triggers
- New API endpoint
- API change
- Usage pattern
- Limitation discovery

---

### 6. Module Knowledge Memory

#### Stored Information
- Module purposes
- Module interactions
- Module dependencies
- Module-specific patterns
- Module history

#### Memory Structure
```markdown
## Module Knowledge
- **Module**: [Module name]
- **Purpose**: [What it does]
- **Interactions**: [How it interacts]
- **Dependencies**: [What it depends on]
- **Status**: [Active/Modified]
```

#### Update Triggers
- New module
- Module change
- Interaction update
- Dependency change

---

### 7. Bug History Memory

#### Stored Information
- Known bugs
- Bug root causes
- Bug fixes
- Bug prevention measures
- Bug patterns

#### Memory Structure
```markdown
## Bug History
- **Bug ID**: [Unique identifier]
- **Description**: [What the bug was]
- **Root Cause**: [Why it happened]
- **Fix**: [How it was fixed]
- **Prevention**: [How to prevent]
- **Status**: [Fixed/Monitoring]
```

#### Update Triggers
- New bug discovered
- Bug fixed
- Root cause identified
- Prevention measure added

---

### 8. Feature History Memory

#### Stored Information
- Implemented features
- Feature requirements
- Feature designs
- Feature implementations
- Feature outcomes

#### Memory Structure
```markdown
## Feature History
- **Feature**: [Feature name]
- **Requirements**: [What was needed]
- **Design**: [How it was designed]
- **Implementation**: [How it was built]
- **Outcome**: [What happened]
- **Status**: [Active/Modified]
```

#### Update Triggers
- New feature implemented
- Feature modified
- Feature outcome
- Feature retirement

---

### 9. Decision History Memory

#### Stored Information
- Architectural decisions
- Technical decisions
- Business decisions
- Process decisions
- Tool decisions

#### Memory Structure
```markdown
## Decision History
- **Decision**: [What was decided]
- **Date**: [When it was decided]
- **Context**: [Why it was decided]
- **Alternatives**: [What else was considered]
- **Impact**: [What was affected]
- **Status**: [Active/Superseded]
```

#### Update Triggers
- New decision made
- Decision reviewed
- Decision superseded
- Decision documented

---

### 10. Development History Memory

#### Stored Information
- Development milestones
- Development patterns
- Development challenges
- Development solutions
- Development lessons

---

### 11. Helper Functions Memory

#### Stored Information
- Available helper functions
- Function signatures
- Usage patterns
- Dependencies

#### Memory Structure
```markdown
## Helper Function
- **Function Name**: [function name]
- **Parameters**: [parameters]
- **Return Type**: [return type]
- **Purpose**: [what it does]
- **Usage**: [how to use]
- **Location**: [file path]
```

#### Known Helper Functions
- `formatCurrency($amount, $currency, $decimals)` - Format currency with commas
- `calculateTax($amount, $taxPercent)` - Calculate tax amount
- `getDayNameFromDate($date)` - Get day name from date
- `amountToWords($amount)` - Convert amount to words
- `normalizeMobile($number)` - Normalize mobile number

#### Memory Structure
```markdown
## Development History
- **Milestone**: [What was achieved]
- **Date**: [When it was achieved]
- **Challenges**: [What was difficult]
- **Solutions**: [How challenges were overcome]
- **Lessons**: [What was learned]
```

#### Update Triggers
- Milestone reached
- Challenge encountered
- Solution found
- Lesson learned

---

## Memory Storage

### File-Based Storage

#### Location
```
.ai/
├── MEMORY.md          # This file
├── ARCHITECTURE.md    # Architecture memory
├── DECISIONS.md       # Decision memory
├── KNOWN_BUGS.md      # Bug memory
├── BUSINESS_RULES.md  # Business memory
├── DATABASE.md        # Database memory
├── ROUTES.md          # API memory
├── API.md             # API memory
└── MODULES/           # Module memory
```

#### Format
- Markdown format for readability
- Structured with headers
- Tagged with dates and status
- Cross-referenced where appropriate

### Graph-Based Storage

#### Location
```
.ai/GRAPH_MEMORY.md
```

#### Structure
- Component relationships
- Dependency graphs
- Interaction maps
- Data flow diagrams

---

## Memory Operations

### 1. Store Memory

#### Process
1. Identify information to store
2. Categorize information
3. Format according to structure
4. Add to appropriate file
5. Update cross-references
6. Verify consistency

#### Validation
- Check for duplicates
- Verify accuracy
- Ensure completeness
- Validate format

### 2. Retrieve Memory

#### Process
1. Identify memory need
2. Search relevant files
3. Filter by category
4. Extract information
5. Validate relevance
6. Apply to current context

#### Validation
- Verify accuracy
- Check relevance
- Ensure completeness
- Validate applicability

### 3. Update Memory

#### Process
1. Identify outdated information
2. Verify current state
3. Update memory files
4. Maintain consistency
5. Archive old information
6. Document changes

#### Validation
- Check for conflicts
- Verify accuracy
- Ensure consistency
- Validate completeness

### 4. Delete Memory

#### Process
1. Identify obsolete information
2. Verify obsolescence
3. Archive if needed
4. Remove from active memory
5. Update references
6. Document deletion

#### Validation
- Verify obsolescence
- Check dependencies
- Ensure no impact
- Validate deletion

---

## Memory Management Rules

### Rule 1: Always Document
Every significant change must be documented in memory.

### Rule 2: Keep Current
Memory must be kept current and accurate.

### Rule 3: Avoid Duplicates
Memory must not contain duplicate information.

### Rule 4: Cross-Reference
Memory must be cross-referenced where appropriate.

### Rule 5: Version Control
Memory must be version controlled with Git.

### Rule 6: Backup Regularly
Memory must be backed up regularly.

### Rule 7: Validate Always
Memory must be validated before use.

### Rule 8: Clean Up
Memory must be cleaned up regularly.

---

## Memory Usage Patterns

### Pattern 1: On Task Start
1. Load relevant memory
2. Check for similar past tasks
3. Apply lessons learned
4. Update memory with new context

### Pattern 2: On Task Complete
1. Document what was done
2. Record decisions made
3. Note challenges encountered
4. Update memory with outcomes

### Pattern 3: On Bug Fix
1. Document the bug
2. Record root cause
3. Document fix
4. Add prevention measures

### Pattern 4: On Feature Implementation
1. Document requirements
2. Record design decisions
3. Document implementation
4. Note lessons learned

### Pattern 5: On Architecture Change
1. Document the change
2. Record rationale
3. Document impact
4. Update related memory

---

## Memory Maintenance

### Daily Maintenance
- Review and update active memory
- Clean up outdated information
- Verify consistency
- Archive completed tasks

### Weekly Maintenance
- Review memory structure
- Update documentation
- Clean up duplicates
- Optimize organization

### Monthly Maintenance
- Review memory completeness
- Archive old information
- Update cross-references
- Optimize storage

### Quarterly Maintenance
- Review memory strategy
- Update memory structure
- Optimize performance
- Plan improvements

---

## Memory Security

### Sensitive Information
- Never store credentials in memory
- Use environment variables for secrets
- Encrypt sensitive memory
- Audit memory access

### Access Control
- Limit memory access to authorized agents
- Log memory operations
- Monitor memory usage
- Protect memory integrity

### Data Protection
- Backup memory regularly
- Encrypt backups
- Test backup restoration
- Protect backup integrity

---

## Memory Performance

### Optimization Strategies
1. **Index Memory** - Create indexes for fast retrieval
2. **Cache Frequent Access** - Cache frequently accessed memory
3. **Compress Old Memory** - Archive old memory to reduce size
4. **Parallel Operations** - Use parallel operations for bulk updates
5. **Lazy Loading** - Load memory only when needed

### Performance Monitoring
1. **Track Access Times** - Monitor memory access performance
2. **Measure Size** - Track memory size growth
3. **Profile Operations** - Profile memory operations
4. **Optimize Queries** - Optimize memory queries

---

## Memory Quality

### Quality Metrics
1. **Accuracy** - Is memory information correct?
2. **Completeness** - Is memory comprehensive?
3. **Consistency** - Is memory consistent across files?
4. **Relevance** - Is memory relevant to current context?
5. **Timeliness** - Is memory up to date?

### Quality Assurance
1. **Regular Reviews** - Review memory quality regularly
2. **Validation Checks** - Validate memory accuracy
3. **Consistency Checks** - Check memory consistency
4. **User Feedback** - Collect feedback on memory quality

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
