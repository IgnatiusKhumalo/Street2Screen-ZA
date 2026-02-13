# 🎯 STEP-BY-STEP DATABASE SETUP GUIDE

**Student:** Ignatius Mayibongwe Khumalo  
**Project:** Street2Screen ZA  
**Institution:** Eduvos Private Institution

---

## 📊 WHAT YOU HAVE NOW:

### ✅ COMPLETED VISUALS:
1. **VISUAL_CRC_Cards.html** - All 15 classes beautifully displayed
2. **VISUAL_Context_Diagram.html** - System boundary diagram
3. **VISUAL_DFD_Level1.html** - Major processes diagram
4. **VISUAL_Use_Case_Diagram.html** - Actor interactions

### ✅ DOCUMENTATION FILES:
1. D2_CRC_Cards.md
2. D2_EERD_Documentation.md
3. D2_Context_Diagram.md
4. D2_Data_Flow_Diagrams.md
5. D2_Use_Case_Diagram.md
6. **database_schema.sql** ← WE'LL USE THIS NOW!

---

## 🗄️ DATABASE SETUP - STEP BY STEP

### **STEP 1: Open XAMPP**
1. Click Windows Start Menu
2. Type "XAMPP Control Panel"
3. Click to open
4. Click "Start" next to **Apache**
5. Click "Start" next to **MySQL**
6. Wait for both to turn GREEN

### **STEP 2: Open phpMyAdmin**
1. Click "Admin" button next to MySQL in XAMPP
2. **OR** open browser and go to: `http://localhost/phpmyadmin`
3. You should see the phpMyAdmin dashboard

### **STEP 3: Prepare the SQL File**
1. Open `database_schema.sql` (the file I created for you)
2. Press `Ctrl + A` (Select All)
3. Press `Ctrl + C` (Copy)

### **STEP 4: Run the SQL**
1. In phpMyAdmin, click the **"SQL"** tab at the top
2. You'll see a big text box
3. Press `Ctrl + V` (Paste the entire SQL file contents)
4. Scroll down and click the **"Go"** button (bottom right)
5. Wait 5-10 seconds...

### **STEP 5: Verify Success**
✅ **YOU SHOULD SEE:**
- Green checkmark ✔
- Message: "15 rows affected"
- Message: "Database street2screen_db created"

❌ **IF YOU SEE ERRORS:**
- Red X or error message
- Take screenshot and show me - I'll help fix it!

### **STEP 6: View Your Tables**
1. Click "street2screen_db" in left sidebar
2. You should see **15 tables**:
   - admin_logs
   - categories
   - conversations
   - disputes
   - messages
   - orders
   - password_resets
   - product_images
   - products
   - reviews
   - sessions
   - transactions
   - translations
   - users
   - verification_documents

3. Click on "users" table
4. Click "Browse" tab
5. You should see **1 admin user** already created!
   - Email: admin@street2screen.co.za
   - Password: Admin@2026!

---

## 🎯 WHAT TO DO WITH THE SQL FILE

### **Option A: Copy-Paste (RECOMMENDED - WHAT WE JUST DID)**
✅ Just copy entire contents and paste into phpMyAdmin SQL tab
✅ Click "Go"
✅ Done!

### **Option B: Import File**
1. Save `database_schema.sql` to your computer
2. In phpMyAdmin, click "Import" tab
3. Click "Choose File"
4. Select `database_schema.sql`
5. Click "Go" at bottom
6. Wait for completion

**Both methods work - Option A is faster!**

---

## 📸 TAKE SCREENSHOTS FOR D2 SUBMISSION

After database is set up, take these screenshots:

### **Screenshot 1: Table List**
1. Click "street2screen_db" in left sidebar
2. All 15 tables visible
3. Press `Windows + Shift + S`
4. Capture the screen
5. Save as: `MySQL_Tables_List.png`

### **Screenshot 2: Users Table Structure**
1. Click "users" table
2. Click "Structure" tab
3. Shows all columns (user_id, full_name, email, etc.)
4. Screenshot this
5. Save as: `MySQL_Users_Table_Structure.png`

### **Screenshot 3: Sample Data**
1. Click "users" table
2. Click "Browse" tab
3. Shows the admin user
4. Screenshot this
5. Save as: `MySQL_Sample_Data.png`

---

## 🚀 NEXT STEPS AFTER DATABASE SETUP

Once your database is running, we'll move to:

### **1. GITHUB UPDATE** (5 minutes)
Commit all our diagrams and documentation:
```bash
cd C:\xampp\htdocs\street2screen
git add .
git commit -m "Add D2 Phase 1: All design diagrams and database schema"
git push origin main
```

### **2. HTML PROTOTYPES** (We'll create these next)
- Homepage mockup (mobile/tablet/desktop)
- Product listing page
- Product detail page
- Seller dashboard
- Admin panel
- All using simple HTML + Bootstrap (no Figma needed!)

### **3. START CODING** (After prototypes)
- Authentication system (registration, login)
- Product listing features
- Payment integration
- And more...

---

## ❓ TROUBLESHOOTING

### **Problem: MySQL won't start in XAMPP**
**Solution:**
1. Click "Config" next to MySQL
2. Click "my.ini"
3. Find line: `port=3306`
4. Change to: `port=3307`
5. Save file
6. Try starting MySQL again

### **Problem: "Database already exists" error**
**Solution:**
1. In phpMyAdmin left sidebar
2. Click "street2screen_db"
3. Click "Operations" tab
4. Scroll down, click "Drop the database"
5. Confirm
6. Run the SQL script again

### **Problem: Triggers not creating**
**Solution:**
This is okay! Triggers are optional. The database will still work.
Just continue with the setup.

---

## ✅ CHECKLIST

Before moving to next phase, confirm:

- [ ] XAMPP Apache is running (GREEN)
- [ ] XAMPP MySQL is running (GREEN)
- [ ] phpMyAdmin opens successfully
- [ ] Database "street2screen_db" exists
- [ ] All 15 tables visible in database
- [ ] Admin user exists in users table
- [ ] Screenshots taken for D2 submission

---

## 📞 READY FOR NEXT STEP?

Once you've completed the database setup, tell me:

**Option A:** "Database is set up! Let's update GitHub now"  
**Option B:** "Database is set up! Let's start making prototypes"  
**Option C:** "I'm having issues with [describe problem]"

I'm here to help every step of the way! 🚀

---

**Document Created:** February 12, 2026  
**Student:** Ignatius Mayibongwe Khumalo  
**Institution:** Eduvos Private Institution
