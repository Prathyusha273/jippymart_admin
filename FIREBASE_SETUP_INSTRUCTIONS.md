# Firebase Setup Instructions for Activity Logs

## 🔥 **Step 1: Create Firebase Project**

1. **Go to Firebase Console**
   - Visit: https://console.firebase.google.com/
   - Sign in with your Google account

2. **Create New Project**
   - Click "Create a project"
   - Enter project name: `jippymart-admin-logs` (or your preferred name)
   - Enable Google Analytics (optional)
   - Click "Create project"

3. **Enable Firestore Database**
   - In project dashboard, click "Firestore Database"
   - Click "Create database"
   - Choose "Start in test mode" (we'll add security rules later)
   - Select a location close to your users
   - Click "Done"

## 🔑 **Step 2: Generate Service Account Key**

1. **Go to Project Settings**
   - Click the gear icon next to "Project Overview"
   - Select "Project settings"

2. **Service Accounts Tab**
   - Click "Service accounts" tab
   - Click "Generate new private key"
   - Click "Generate key"
   - Download the JSON file

3. **Save the Key File**
   ```bash
   # Create the directory
   mkdir -p storage/app/firebase
   
   # Move the downloaded file
   mv ~/Downloads/your-project-firebase-adminsdk-xxxxx.json storage/app/firebase/serviceAccount.json
   ```

## ⚙️ **Step 3: Configure Environment Variables**

Add these to your `.env` file:

```env
# Firebase Configuration
FIRESTORE_PROJECT_ID=your-project-id-here
FIRESTORE_DATABASE_ID=(default)
FIRESTORE_COLLECTION=activity_logs
FIRESTORE_TIMEOUT=30
FIRESTORE_RETRY_INITIAL_DELAY=1.0
FIRESTORE_RETRY_MAX_DELAY=60.0
FIRESTORE_RETRY_MULTIPLIER=2.0
```

**Replace `your-project-id-here` with your actual Firebase project ID**

## 🔐 **Step 4: Set Up Security Rules**

1. **Go to Firestore Database**
   - Click "Rules" tab
   - Replace the rules with:

```javascript
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    // Activity logs collection
    match /activity_logs/{document} {
      allow read: if request.auth != null;
      allow write: if request.auth != null;
    }
    
    // Allow access to existing collections
    match /{document=**} {
      allow read, write: if true;
    }
  }
}
```

2. **Click "Publish"**

## 🧪 **Step 5: Test the Setup**

1. **Run the test script:**
   ```bash
   php test_activity_logs.php
   ```

2. **Expected output:**
   ```
   🧪 Activity Log System Test
   ==========================
   
   1. Testing ActivityLogger class...
   ✅ ActivityLogger class instantiated successfully
   
   2. Testing configuration...
   ✅ Project ID configured: your-project-id
   ✅ Credentials file exists: /path/to/serviceAccount.json
   
   3. Testing logging functionality...
   ✅ Log entry created successfully
   ```

## 🌐 **Step 6: Configure Frontend Firebase**

1. **Get Web App Configuration**
   - In Firebase Console, click "Project settings"
   - Scroll down to "Your apps"
   - Click the web icon (</>)
   - Register app with name: "JippyMart Admin"
   - Copy the config object

2. **Update Activity Logs Page**
   - Open `resources/views/activity_logs/index.blade.php`
   - Find the Firebase config section
   - Replace the placeholder with your actual config:

```javascript
const firebaseConfig = {
  apiKey: "your-api-key",
  authDomain: "your-project.firebaseapp.com",
  projectId: "your-project-id",
  storageBucket: "your-project.appspot.com",
  messagingSenderId: "123456789",
  appId: "your-app-id"
};
```

## 🚀 **Step 7: Test Real-time Logging**

1. **Visit Activity Logs Page**
   - Go to `/activity-logs` in your admin panel
   - You should see the real-time logs page

2. **Test with Cuisines Module**
   - Go to `/cuisines`
   - Create a new cuisine
   - Check `/activity-logs` for real-time updates

3. **Expected Behavior**
   - Logs appear immediately without page refresh
   - All user actions are tracked
   - IP address and user agent are captured

## 🔍 **Troubleshooting**

### **Common Issues:**

1. **"Service account key not found"**
   - Ensure the JSON file is in `storage/app/firebase/serviceAccount.json`
   - Check file permissions

2. **"Project ID not configured"**
   - Verify `FIRESTORE_PROJECT_ID` in `.env` file
   - Restart your web server after changing `.env`

3. **"Permission denied"**
   - Check Firestore security rules
   - Ensure rules allow read/write for authenticated users

4. **"Real-time updates not working"**
   - Verify Firebase config in activity logs page
   - Check browser console for errors

### **Debug Commands:**

```bash
# Test Firestore connection
php artisan tinker
>>> app(\App\Services\ActivityLogger::class)->log(auth()->user(), 'test', 'test', 'Test log');

# Check configuration
php artisan config:cache
php artisan config:clear
```

## 📊 **Monitoring Setup**

1. **Set Up Billing Alerts**
   - In Firebase Console, go to "Usage and billing"
   - Set up budget alerts to avoid unexpected charges

2. **Monitor Usage**
   - Check Firestore usage in Firebase Console
   - Monitor read/write operations

## ✅ **Verification Checklist**

- [ ] Firebase project created
- [ ] Firestore database enabled
- [ ] Service account key downloaded and placed
- [ ] Environment variables configured
- [ ] Security rules published
- [ ] Test script passes
- [ ] Frontend Firebase config updated
- [ ] Real-time logging working
- [ ] Cuisines module tested

---

**Status**: Ready for Firebase configuration
**Next**: Follow the steps above to complete setup
