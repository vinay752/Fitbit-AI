# 🧠 GenAI-Powered Fitbit Health Insight System

A secure, full-stack PHP application that connects to the Fitbit API, fetches and stores user health data, and uses Google's Gemini (RAG) model to generate personalized fitness insights via a conversational chat interface.

## 🚀 Features

- 🔒 **Secure OAuth2 Integration** with Fitbit API
- 📊 **Daily Data Sync** for steps, sleep, heart rate, and more (via `fetch_fitbit_data.php`)
- 🧠 **Gemini RAG (Retrieval-Augmented Generation)** integration for natural language insights
- 💬 **Chat Interface** (`index.php`) to ask health-related questions and get AI-generated answers
- 🗄️ **Modular Architecture** with clear separation of public UI, scripts, and secure backend logic
- 🗃️ Local **MySQL database (phpMyAdmin)** for storing historical health data

---

## 📁 Project Structure


fitbit/ <br>
├── public/<br>
│ ├── index.php # Chat interface for user queries<br>
│ └── authorize.php # OAuth2 login and Fitbit authorization<br>
├── scripts/<br>
│ └── get_fitness_data.php # Fetches stored data from MySQL<br>
│ ├── callback.php # Fitbit OAuth2 callback handler<br>
│ ├── env.php # Loads .env variables<br>
│ ├── fetch_fitbit_data.php # Syncs Fitbit data daily from Fitbit API<br>
│ ├── model.php # Connects to Gemini RAG for health insights<br>
├── secure/<br>
│ ├── .env # Environment variables (API keys, DB credentials)<br>
│ ├── .htaccess # Protects secure folder<br>
│ └── tokens.json # Stores and refreshes Fitbit OAuth tokens<br>

## 🛠️ Technologies Used

- **PHP** (Backend)
- **Fitbit API** (Health data collection)
- **MySQL / phpMyAdmin** (Local data storage)
- **Gemini Pro via RAG** (Generative AI insights)
- **XAMPP** (Local server environment)
- **OAuth2** (Authentication flow)

---

## 🧪 Example Prompts

Users can ask the system:
- *"How did I sleep last week?"*
- *"What’s my average heart rate trend?"*
- *"Summarize my physical activity for the last 30 days."*

---

## 🛡️ Security

- Sensitive credentials are stored in `.env` and loaded via `env.php`
- OAuth tokens are stored in `tokens.json` with `.htaccess` protection
- Folder structure enforces security separation between public and internal logic

---

## 📅 Automation

Set up a **cron job or Windows task** to run `fetch_fitbit_data.php` once a day to keep the system in sync with Fitbit servers.

---

## 📌 Future Enhancements

- ✅ Add a dashboard view with charts and trend visualizations
- 🔄 Sync with cloud DB (e.g., Firebase, Supabase)
- 📱 Mobile-friendly UI or Progressive Web App
- 🔒 Token refresh and expiry notifications

---

## 👤 Author

Vinay P.  
[LinkedIn](https://linkedin.com/in/vpal) 
