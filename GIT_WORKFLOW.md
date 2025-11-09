# Git & GitHub Workflow Guide

## 📚 Complete Version Control Workflow

### Initial Setup (Already Done! ✅)

```bash
git init
git config user.name "Your Name"
git config user.email "your.email@example.com"
git add .
git commit -m "Initial commit: Sistema de gestión médica PropielEquipo"
```

---

## 🌐 Connecting to GitHub

### 1. Create Repository on GitHub

1. Go to https://github.com
2. Click "+" → "New repository"
3. Repository name: `PropielEquipo` (or your choice)
4. Description: "Sistema de gestión de citas médicas"
5. Keep it **Private** (contains medical data)
6. **DO NOT** initialize with README (we already have one)
7. Click "Create repository"

### 2. Connect Local Repository to GitHub

```bash
# Add remote (use YOUR repository URL from GitHub)
git remote add origin https://github.com/YOUR_USERNAME/PropielEquipo.git

# Verify remote was added
git remote -v

# Push to GitHub (first time)
git push -u origin main
# or if your branch is called 'master':
git push -u origin master
```

---

## 🔄 Daily Workflow

### Making Changes

```bash
# 1. Check current status
git status

# 2. Stage specific files
git add src/Paciente/reservar.php
git add src/Doctor/especialidades/dermatologia/

# Or stage all changes
git add .

# 3. Commit with descriptive message
git commit -m "feat: add real-time search to appointments"

# 4. Push to GitHub
git push
```

### Commit Message Conventions

Use prefixes for clarity:

```bash
git commit -m "feat: add new feature"           # New functionality
git commit -m "fix: resolve bug in login"       # Bug fix
git commit -m "docs: update README"             # Documentation
git commit -m "style: format code"              # Code style changes
git commit -m "refactor: restructure database"  # Code restructuring
git commit -m "test: add unit tests"            # Tests
git commit -m "chore: update dependencies"      # Maintenance
```

---

## 🌿 Branching Strategy

### Creating Feature Branches

```bash
# Create and switch to new branch
git checkout -b feature/patient-history

# Work on your feature...
# Make commits...

# Push feature branch to GitHub
git push -u origin feature/patient-history

# When done, merge back to main
git checkout main
git merge feature/patient-history
git push

# Delete feature branch (optional)
git branch -d feature/patient-history
git push origin --delete feature/patient-history
```

### Recommended Branch Structure

```
main (or master)     ← Production-ready code
  ├── develop        ← Development integration
  │   ├── feature/consent-forms
  │   ├── feature/doctor-dashboard
  │   └── feature/real-time-search
  └── hotfix/login-bug
```

### Common Branch Commands

```bash
# List all branches
git branch -a

# Switch to existing branch
git checkout branch-name

# Create new branch
git branch new-branch-name

# Delete branch
git branch -d branch-name

# Rename current branch
git branch -m new-name
```

---

## 📥 Pulling Changes (Team Collaboration)

```bash
# Fetch changes from GitHub (doesn't merge)
git fetch origin

# Pull changes (fetch + merge)
git pull origin main

# Pull with rebase (cleaner history)
git pull --rebase origin main
```

---

## 🔍 Viewing History

```bash
# View commit history
git log

# Compact view
git log --oneline

# With graph
git log --graph --oneline --all

# View specific file history
git log -- src/database_queries.php

# Show changes in last commit
git show

# Show specific commit
git show COMMIT_HASH
```

---

## ⏪ Undoing Changes

### Before Commit

```bash
# Unstage file (keep changes)
git reset HEAD file.php

# Discard changes in file
git checkout -- file.php

# Discard all changes
git reset --hard HEAD
```

### After Commit

```bash
# Undo last commit (keep changes)
git reset --soft HEAD~1

# Undo last commit (discard changes)
git reset --hard HEAD~1

# Revert commit (creates new commit)
git revert COMMIT_HASH
```

---

## 🏷️ Tags (Versions)

```bash
# Create tag for version
git tag -a v1.0.0 -m "Release version 1.0.0"

# Push tags to GitHub
git push origin --tags

# List all tags
git tag -l

# Delete tag
git tag -d v1.0.0
git push origin :refs/tags/v1.0.0
```

---

## 🚨 Common Issues & Solutions

### Issue: Forgot to add .gitignore before first commit

```bash
# Remove tracked files that should be ignored
git rm --cached src/database_connection.php
git commit -m "Remove sensitive files from tracking"
git push
```

### Issue: Committed sensitive data

```bash
# Use BFG Repo-Cleaner or git-filter-branch
# Better: Delete repo and start fresh with proper .gitignore
```

### Issue: Merge conflicts

```bash
# 1. Git will mark conflicts in files with <<<<<<<, =======, >>>>>>>
# 2. Open files and manually resolve conflicts
# 3. Stage resolved files
git add resolved-file.php

# 4. Complete merge
git commit
```

### Issue: Accidentally committed to main instead of feature branch

```bash
# Move commits to new branch
git branch feature/my-feature
git reset --hard origin/main
git checkout feature/my-feature
```

---

## 👥 Collaboration Workflow

### 1. Fork & Clone (For External Contributors)

```bash
# Clone their fork
git clone https://github.com/THEIR_USERNAME/PropielEquipo.git

# Add original repo as upstream
git remote add upstream https://github.com/YOUR_USERNAME/PropielEquipo.git
```

### 2. Pull Request Workflow

```bash
# 1. Create feature branch
git checkout -b feature/new-specialty

# 2. Make changes and commit
git add .
git commit -m "feat: add cardiology specialty"

# 3. Push to YOUR fork
git push origin feature/new-specialty

# 4. Go to GitHub and create Pull Request

# 5. After PR is approved and merged, update your main
git checkout main
git pull upstream main
git push origin main
```

---

## 📊 GitHub-Specific Features

### Issues

Track bugs and features on GitHub:
- Go to repository → Issues → New Issue
- Link commits to issues: `git commit -m "fix: resolve login bug #42"`

### Projects

Organize work with Kanban boards:
- Go to repository → Projects → New Project
- Add cards for features/bugs
- Move through columns: To Do → In Progress → Done

### Actions (CI/CD)

Automate testing/deployment (advanced):
- Create `.github/workflows/main.yml`
- Define automated tests on push

---

## 🔒 Security Best Practices

### What NOT to Commit

✅ **Safe to commit:**
- Source code (.php, .html, .css, .js)
- Configuration templates (.template.php)
- Documentation (.md)
- Database schema (.sql without data)
- Empty placeholder files (.gitkeep)

❌ **Never commit:**
- Database credentials (database_connection.php)
- Sensitive configuration files
- User uploaded files (images, PDFs)
- Large binary files
- Environment variables (.env)
- API keys or tokens
- Session data

### GitHub Repository Settings

1. Make repository **Private** for medical data
2. Enable "Restrict who can push to matching branches" for `main`
3. Require Pull Request reviews before merging
4. Enable branch protection rules

---

## 📖 Quick Reference

```bash
# Status and Info
git status              # Show working tree status
git log                 # Show commit history
git diff                # Show changes

# Basic Workflow
git add .               # Stage all changes
git commit -m "msg"     # Commit staged changes
git push                # Push to GitHub

# Branching
git branch              # List branches
git checkout -b name    # Create and switch to branch
git merge branch        # Merge branch into current

# Remote Operations
git pull                # Fetch and merge
git fetch               # Download changes
git push                # Upload changes

# Undo
git reset --soft HEAD~1 # Undo last commit (keep changes)
git reset --hard HEAD~1 # Undo last commit (discard changes)
git checkout -- file    # Discard file changes
```

---

## 🎯 Your Next Steps

1. **Update Git config with your real info:**
   ```bash
   git config user.name "Juan Tellez"
   git config user.email "juan@example.com"
   ```

2. **Create GitHub account** (if you don't have one)

3. **Create repository on GitHub**

4. **Connect and push:**
   ```bash
   git remote add origin https://github.com/YOUR_USERNAME/PropielEquipo.git
   git branch -M main  # Rename master to main (if needed)
   git push -u origin main
   ```

5. **Start working with branches:**
   ```bash
   git checkout -b develop
   git push -u origin develop
   ```

---

Need help? Check:
- GitHub Documentation: https://docs.github.com
- Git Book: https://git-scm.com/book
- Git Cheat Sheet: https://education.github.com/git-cheat-sheet-education.pdf
