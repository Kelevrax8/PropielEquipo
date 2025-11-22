# Comparison: Unified vs Per-Doctor Schedules

## Quick Decision Guide

**Choose OPTION 1 (Global Clinic Hours)** if:
- ✅ All doctors work the same hours
- ✅ Hours rarely change
- ✅ Simpler is better
- ✅ Less admin overhead

**Choose OPTION 2 (Per-Doctor Schedules)** if:
- ✅ Doctors may have different schedules in the future
- ✅ Part-time doctors
- ✅ Rotating schedules
- ✅ Flexibility is important

---

## Option 1: Global Clinic Hours (Simplified)

### Pros
- 🟢 **Much simpler** - Only 1 record per day vs. 1 per doctor per day
- 🟢 **Easier admin** - Change once, applies to all doctors
- 🟢 **Less storage** - ~5 records vs. ~25 records (5 doctors × 5 days)
- 🟢 **Faster queries** - No JOIN needed on doctor

### Cons
- 🔴 **No per-doctor flexibility** - Dr. García can't work afternoons only
- 🔴 **Can't have part-time doctors** - Everyone must work same hours
- 🔴 **Future changes harder** - If you need flexibility later, requires migration

### Database Structure
```sql
horarios_clinica (5 records total)
├── lunes: 09:00-18:00
├── martes: 09:00-18:00
├── miercoles: 09:00-18:00
├── jueves: 09:00-18:00
└── viernes: 09:00-18:00

bloqueos_horarios (per doctor)
└── Doctor 1: Dec 20-31 (vacations)
```

### Setup Steps
1. Execute `docs/horarios_clinica_simple.sql`
2. Change `get_available_hours.php` to `get_available_hours_simple.php`
3. Admin panel manages global hours only
4. Individual doctors can still block their own dates

### Use Cases
- Small clinic with 2-3 doctors
- All doctors are full-time
- Consistent business hours
- Simple operations

---

## Option 2: Per-Doctor Schedules (Current Implementation)

### Pros
- 🟢 **Full flexibility** - Each doctor can have unique hours
- 🟢 **Part-time support** - Dr. A works Mon-Wed, Dr. B works Thu-Fri
- 🟢 **Rotating schedules** - Dr. C works mornings, Dr. D afternoons
- 🟢 **Future-proof** - Easy to customize later

### Cons
- 🔴 **More complex** - More records to manage
- 🔴 **Admin overhead** - Must configure each doctor
- 🔴 **Slightly slower** - More database queries
- 🔴 **More setup time** - Initial configuration takes longer

### Database Structure
```sql
horarios_doctor (25 records for 5 doctors)
Doctor 1
├── lunes: 09:00-18:00
├── martes: 09:00-18:00
└── ...
Doctor 2
├── lunes: 09:00-18:00
└── ...
```

### Setup Steps
1. Execute `docs/horarios_doctor_schema.sql` (already done)
2. Use existing `get_available_hours.php`
3. Admin configures each doctor individually
4. OR use `docs/apply_schedule_to_all_doctors.sql` for bulk setup

### Use Cases
- Multiple doctors with different schedules
- Part-time or rotating schedules
- Growing practice (adding doctors frequently)
- Complex scheduling needs

---

## Hybrid Approach (Best of Both Worlds)

**Start simple, migrate later:**

1. **Now:** Use Option 1 (Global Hours)
   - Quick setup
   - Works for current needs
   
2. **Later:** Migrate to Option 2 when needed
   ```sql
   -- Migration: Copy global to per-doctor
   INSERT INTO horarios_doctor (id_doctor, dia_semana, hora_inicio, hora_fin, intervalo_minutos)
   SELECT 
       u.user_id,
       h.dia_semana,
       h.hora_inicio,
       h.hora_fin,
       h.intervalo_minutos
   FROM horarios_clinica h
   CROSS JOIN usuarios u
   WHERE u.rol = 1;
   ```

---

## Quick Comparison Table

| Feature | Global Hours | Per-Doctor |
|---------|-------------|------------|
| Records in DB | ~5 | ~25 (5 doctors) |
| Setup time | 2 min | 10 min |
| Admin complexity | Low | Medium |
| Flexibility | Low | High |
| Query speed | Faster | Slightly slower |
| Future changes | Hard | Easy |
| Part-time doctors | ❌ No | ✅ Yes |
| Rotating schedules | ❌ No | ✅ Yes |
| Individual blocks | ✅ Yes | ✅ Yes |

---

## My Recommendation

### For Your Current Setup:

Looking at your system, I recommend **Option 2 (Per-Doctor)** because:

1. **Already implemented** - You have the full system ready
2. **Minimal overhead** - Even with 10 doctors, only ~50 records
3. **Future flexibility** - As clinic grows, you'll need this
4. **Easy bulk setup** - Use `apply_schedule_to_all_doctors.sql` to set same hours for everyone NOW

### Quick Win:

```sql
-- Execute this to give all doctors the same hours RIGHT NOW:
SOURCE docs/apply_schedule_to_all_doctors.sql;

-- Result: All doctors have Mon-Fri 9-6, but you CAN customize later
```

This gives you:
- ✅ Uniform hours today
- ✅ Flexibility tomorrow
- ✅ No code changes needed
- ✅ One SQL script to run

---

## Implementation Examples

### Option 1: Change Global Hours
```sql
-- Everyone now works 8 AM - 8 PM
UPDATE horarios_clinica
SET hora_inicio = '08:00:00',
    hora_fin = '20:00:00';
```

### Option 2: Customize One Doctor
```sql
-- Dr. García (ID=5) works afternoons only
UPDATE horarios_doctor
SET hora_inicio = '14:00:00',
    hora_fin = '20:00:00'
WHERE id_doctor = 5;
```

---

## Bottom Line

**For uniform hours with flexibility later:**  
→ Keep current system, run `apply_schedule_to_all_doctors.sql`

**For maximum simplicity forever:**  
→ Switch to `horarios_clinica_simple.sql`

**Need help deciding?**  
→ Go with Option 2 (current system), it's already done! 🎉
