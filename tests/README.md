# Test Data Setup Guide

This directory contains tools and sample data for development/testing.

## 🎯 Purpose

Test data allows developers to:
- Work without access to real patient data (privacy/legal compliance)
- Have consistent data across all development environments
- Test edge cases with controlled data

## 📁 Structure

```
tests/
├── sample_data/           # Sample files (committed to Git)
│   ├── images/           # Fake medical images
│   └── pdfs/             # Fake consent forms
├── generate_test_data.php # Script to create test files
└── seed_database.sql     # SQL to populate test database
```

## 🚀 Quick Setup

### 1. Generate Test Files

```bash
php tests/generate_test_data.php
```

This creates:
- 5 test images in `src/Images/ImgMedicas/`
- 3 test PDFs in `src/consentimientos/`

### 2. Seed Test Database

```bash
mysql -u root -p propielequipo < tests/seed_database.sql
```

This creates:
- 5 test patients (phone: 0000000001 to 0000000005)
- Test appointments
- Test medical image records

### 3. Test Login Credentials

```
Phone: 0000000001
Password: test123 (or whatever you set in seed script)
```

## 🔒 Important Notes

### What's Safe to Commit:

✅ **DO commit:**
- Test data generation scripts
- Small sample images (anonymized)
- Fake PDF templates
- Database seeding scripts
- This README

❌ **DON'T commit:**
- Real patient images
- Real consent forms
- Production database dumps
- Any file with real patient identifiable information

### Development vs. Production

| Environment | Data Source | Purpose |
|-------------|-------------|---------|
| **Development** | Fake/synthetic | Safe for all developers |
| **Staging** | Anonymized subset | Testing with realistic data |
| **Production** | Real data | Live system (restricted access) |

## 🎨 Creating Better Test Images

For more realistic test images:

```bash
# Download medical stock photos (royalty-free)
# Sites: Unsplash, Pexels, Pixabay
# Search: "skin condition", "foot care", etc.

# Or use placeholder services:
# https://placehold.co/400x300.jpg
# https://picsum.photos/400/300
```

## 📄 Creating Better Test PDFs

Use a PDF library for proper PDF generation:

```bash
# Install FPDF
composer require setasign/fpdf

# Or use TCPDF
composer require tecnickcom/tcpdf
```

Then update `generate_test_data.php` to create real PDFs.

## 🧹 Cleanup

Remove all test data:

```bash
# Remove test files
rm -f src/Images/ImgMedicas/test_image_*.jpg
rm -f src/consentimientos/consentimiento_*_usuario_test*.pdf

# Remove test database records
mysql -u root -p propielequipo -e "DELETE FROM usuarios WHERE telefono LIKE '0000000%';"
mysql -u root -p propielequipo -e "DELETE FROM citas WHERE notas LIKE 'Test appointment%';"
```

## 🔄 Updating Test Data

When database schema changes:

1. Update `seed_database.sql`
2. Re-run seeding: `mysql -u root -p propielequipo < tests/seed_database.sql`
3. Commit updated seed script

## 🐳 Using Docker (Optional)

For isolated development environment:

```bash
# Start containers with pre-seeded data
docker-compose up -d

# Access at http://localhost:8080
```

---

**Remember:** Test data is for development only. Never use real patient data for testing!
