<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List</title>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetch('http://localhost/form_project/backend/process.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    const userList = document.getElementById('user-list');

                    if (data.error) {
                        userList.innerHTML = `<p style="color: red;">Error: ${data.error}</p>`;
                        return;
                    }

                    if (data.length > 0) {
                        let output = '<table border="1"><tr><th>ID</th><th>Full Name</th><th>Email</th></tr>';
                        data.forEach(user => {
                            output += `<tr><td>${user.id}</td><td>${user.fullname}</td><td>${user.email}</td></tr>`;
                        });
                        output += '</table>';
                        userList.innerHTML = output;
                    } else {
                        userList.innerHTML = '<p>No registered users found.</p>';
                    }
                })
                .catch(error => {
                    document.getElementById('user-list').innerHTML = `<p style="color: red;">Error: ${error.message}</p>`;
                    console.error('Error fetching user data:', error);
                });
        });
    </script>
</head>
<body>
    <h1>Registered Users</h1>
    <div id="user-list">Loading...</div>
</body>
</html>

