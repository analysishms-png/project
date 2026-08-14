# Analysis HMS - Auto-Memory System

## 🧠 Auto-Memory Overview

This document defines the automatic memory update system. **Every code change MUST trigger memory updates.**

---

## 🔄 Auto-Memory Rules

### ⚠️ MANDATORY: Memory Update Trigger

**Every time you modify ANY code file, you MUST update the AI memory.**

This is not optional. This is mandatory.

---

## 📋 Memory Update Matrix

### When You Change a Controller:

| File to Update | What to Add |
|----------------|-------------|
| `CHANGELOG.md` | New route, method, functionality |
| `ROUTES.md` | New/modified routes |
| `API.md` | If API endpoint changed |
| `MODULES/*.md` | Module-specific documentation |
| `MEMORY.md` | New patterns learned |

### When You Change a Model:

| File to Update | What to Add |
|----------------|-------------|
| `CHANGELOG.md` | New relationship, scope, accessor |
| `DATABASE.md` | Table changes, relationships |
| `GRAPH_MEMORY.md` | Component relationships |
| `MEMORY.md` | New data patterns |

### When You Change a Migration:

| File to Update | What to Add |
|----------------|-------------|
| `CHANGELOG.md` | Schema changes |
| `DATABASE.md` | New tables, columns, indexes |
| `MEMORY.md` | Database structure updates |

### When You Fix a Bug:

| File to Update | What to Add |
|----------------|-------------|
| `CHANGELOG.md` | Bug fix details |
| `KNOWN_BUGS.md` | Mark bug as resolved |
| `DECISIONS.md` | If decision was made |
| `MEMORY.md` | Root cause, prevention |

### When You Add a Feature:

| File to Update | What to Add |
|----------------|-------------|
| `CHANGELOG.md` | Feature description |
| `BUSINESS_RULES.md` | New business rules |
| `DECISIONS.md` | Architecture decisions |
| `MEMORY.md` | Feature patterns |

### When You Change Business Logic:

| File to Update | What to Add |
|----------------|-------------|
| `CHANGELOG.md` | Logic changes |
| `BUSINESS_RULES.md` | Updated rules |
| `DECISIONS.md` | If decision made |
| `MEMORY.md` | Business patterns |

---

## 📝 Memory Update Templates

### Template 1: Controller Change

```markdown
## CHANGELOG Entry
- **Date**: YYYY-MM-DD
- **File**: app/Http/Controllers/ControllerName.php
- **Change**: Added/Modified [method name]
- **Purpose**: [Why the change was made]
- **Impact**: [What was affected]

## ROUTES.md Update
| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET/POST | /new-route | Controller@method | route.name |

## MODULES/*.md Update
### [Module Name]
- Added new method: `methodName`
- Purpose: [Description]
- Routes: [Related routes]
```

### Template 2: Model Change

```markdown
## CHANGELOG Entry
- **Date**: YYYY-MM-DD
- **File**: app/Models/ModelName.php
- **Change**: Added [relationship/scope/accessor]
- **Purpose**: [Why the change was made]
- **Impact**: [What was affected]

## DATABASE.md Update
### Table: table_name
- Added column: column_name (type)
- Added relationship: relationshipName

## GRAPH_MEMORY.md Update
ModelName ──────▶ RelatedModel (relationship type)
```

### Template 3: Bug Fix

```markdown
## CHANGELOG Entry
- **Date**: YYYY-MM-DD
- **File**: [affected files]
- **Change**: Fixed bug [bug description]
- **Root Cause**: [why it happened]
- **Solution**: [how it was fixed]
- **Impact**: [what was affected]

## KNOWN_BUGS.md Update
### BUG-XXX: [Bug Title]
- **Status**: ✅ RESOLVED
- **Resolution**: [How it was fixed]
- **Date Resolved**: YYYY-MM-DD

## DECISIONS.md Update (if applicable)
### Decision: [Decision title]
- **Context**: [Why decision was needed]
- **Decision**: [What was decided]
- **Rationale**: [Why this decision]
```

### Template 4: New Feature

```markdown
## CHANGELOG Entry
- **Date**: YYYY-MM-DD
- **Feature**: [Feature name]
- **Description**: [What the feature does]
- **Files Created**: [list of new files]
- **Files Modified**: [list of modified files]
- **Routes Added**: [new routes]
- **Database Changes**: [schema changes]

## BUSINESS_RULES.md Update
### Rule: [Rule name]
- **Module**: [Which module]
- **Rule**: [What the rule is]
- **Implementation**: [How it's implemented]
- **Edge Cases**: [Special cases]

## DECISIONS.md Update
### Decision: [Decision title]
- **Date**: YYYY-MM-DD
- **Context**: [Why decision was needed]
- **Decision**: [What was decided]
- **Alternatives**: [What else was considered]
- **Rationale**: [Why this decision]
```

---

## 🔄 Auto-Memory Workflow

### Step 1: Before Code Change
```markdown
1. Read relevant .ai/ files
2. Understand existing patterns
3. Plan your approach
4. Document planned changes
```

### Step 2: During Code Change
```markdown
1. Follow coding standards
2. Implement changes
3. Write tests
4. Document code
```

### Step 3: After Code Change
```markdown
1. Run tests
2. Verify changes work
3. UPDATE ALL RELEVANT .ai/ FILES
4. Verify memory is current
```

---

## 📊 Memory Update Checklist

### Every Change MUST Update:

- [ ] `CHANGELOG.md` - Add entry
- [ ] `MEMORY.md` - Update project knowledge

### Additionally Update (If Applicable):

- [ ] `DECISIONS.md` - If decision made
- [ ] `KNOWN_BUGS.md` - If bug fixed
- [ ] `ARCHITECTURE.md` - If architecture changed
- [ ] `DATABASE.md` - If database changed
- [ ] `API.md` - If API changed
- [ ] `ROUTES.md` - If routes changed
- [ ] `BUSINESS_RULES.md` - If business logic changed
- [ ] `MODULES/*.md` - If module changed
- [ ] `GRAPH_MEMORY.md` - If relationships changed

---

## 🚨 Critical Rules

### Rule 1: NEVER Skip Memory Update
```
❌ Code change without memory update = INCOMPLETE
✅ Code change WITH memory update = COMPLETE
```

### Rule 2: ALWAYS Update CHANGELOG
```
Every single code change MUST have a CHANGELOG entry.
No exceptions.
```

### Rule 3: ALWAYS Update MEMORY.md
```
Every code change MUST update project knowledge.
No exceptions.
```

### Rule 4: Be Specific
```
❌ "Fixed bug" (too vague)
✅ "Fixed room status not updating after check-in by adding proper status validation in ReservationController::checkin()" (specific)
```

### Rule 5: Include Context
```
❌ "Added method" (no context)
✅ "Added calculateTax() method to helpers to calculate GST at 18% for invoice generation" (with context)
```

---

## 📁 Memory File Structure

```
.ai/
├── CHANGELOG.md          # Auto-updated with every change
├── MEMORY.md             # Auto-updated with every change
├── DECISIONS.md          # Updated when decisions made
├── KNOWN_BUGS.md         # Updated when bugs fixed
├── ARCHITECTURE.md       # Updated when architecture changes
├── DATABASE.md           # Updated when database changes
├── API.md                # Updated when API changes
├── ROUTES.md             # Updated when routes change
├── BUSINESS_RULES.md     # Updated when business logic changes
├── GRAPH_MEMORY.md       # Updated when relationships change
└── MODULES/
    ├── FRONT_OFFICE.md   # Updated when module changes
    ├── POS.md            # Updated when module changes
    ├── INVENTORY.md      # Updated when module changes
    ├── FINANCE.md        # Updated when module changes
    └── HOUSEKEEPING.md   # Updated when module changes
```

---

## 🎯 Auto-Memory Summary

### The Golden Rule:

**NO CODE CHANGE IS COMPLETE WITHOUT MEMORY UPDATE!**

### Remember:

1. **BEFORE**: Read existing memory
2. **DURING**: Follow patterns
3. **AFTER**: Update ALL relevant memory files

### This Ensures:

- AI always knows current project state
- No knowledge is lost
- Consistent development
- Easy onboarding
- Better debugging

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
