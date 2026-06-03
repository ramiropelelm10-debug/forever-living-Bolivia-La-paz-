<!DOCTYPE html>
<html lang="es">
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 min-h-screen font-sans">
    
    <div id="app" class="max-w-6xl mx-auto p-6" v-cloak>
        
        <login-component v-if="!isLoggedIn" @login-success="isLoggedIn = true; view = 'catalog'"></login-component>

        <div v-else>
            <productos-component v-if="view === 'catalog'"></productos-component>
            <ventas-component v-if="view === 'sales'"></ventas-component>
            <usuarios-component v-if="view === 'clients' || view === 'requests' || view === 'users_manage'"></usuarios-component>
        </div>

    </div>

</body>
</html>