<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if user not logged in
if (!isset($_SESSION['id'], $_SESSION['user'], $_SESSION['user_email'], $_SESSION['user_role'])) {
    header("Location: index.php");
    exit();
}

// Update the last activity timestamp
$_SESSION['last_activity'] = time();

// Include necessary files
include "db_conn.php";
include 'php/User.php';
include 'actions/add_request.php';
include 'actions/approve_request.php';
include 'actions/delete_request.php';
include 'actions/reject_request.php';
include 'actions/pickup_request.php';


// Fetch user details based on session user ID
$user = getUserById($_SESSION['id'], $conn);

// Define role names
$role_names = [
    555 => 'Director of Operations',
    444 => 'Administration Manager',
    333 => 'Stock Controller',
    222 => 'Branch Manager',
    111 => 'Branch Maker',
    100 => 'Super Admin'
];

// Retrieve session values safely
$user_email = $_SESSION['user_email'] ?? 'Guest';
$user_role = $_SESSION['user_role'] ?? 111;  // Default to 'Admin' role if not set
$role_display = $role_names[$user_role] ?? 'User';

// Fetch user data based on role
if ($user_role == 444) { // Admin role
    $sql = "SELECT * FROM users";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $userList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else { // Normal user
    $sql = "SELECT * FROM users WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $_SESSION['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Create requests table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  item_name VARCHAR(255) NOT NULL,
  quantity INT NOT NULL,
  request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status VARCHAR(255) DEFAULT 'Pending'
)") or die("Error creating table: " . $conn->error);

// Fetch request counts
function fetchCount($conn, $query, $param = null) {
    $stmt = $conn->prepare($query);
    if ($param) $stmt->bindParam(1, $param);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
}

$totalRequests = fetchCount($conn, "SELECT COUNT(*) AS count FROM requests");
$approvedRequests = fetchCount($conn, "SELECT COUNT(*) AS count FROM requests WHERE status = ?", 'Approved');
$pendingRequests = fetchCount($conn, "SELECT COUNT(*) AS count FROM requests WHERE status = ?", 'Pending');

// Add Request



// Approve Requesthere

// Reject Request

// Pickup Request


// Logout
function logout() {
    session_unset();  // Remove all session variables
    session_destroy();  // Destroy the session
    header("Location: index.php");  // Redirect to login page
    exit();
}

// Utility function to check request status
function checkRequestStatus($id, $conn) {
    $statusStmt = $conn->prepare("SELECT status FROM requests WHERE id = :id");
    $statusStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $statusStmt->execute();
    return $statusStmt->fetchColumn();
}

// Utility function to update request status
function updateRequestStatus($id, $status, $conn) {
    $stmt = $conn->prepare("UPDATE requests SET status = :status WHERE id = :id");
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}

// Execute the corresponding function based on the POST action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_request'])) {
        addRequest($conn);
    } elseif (isset($_POST['delete_id'])) {
        deleteRequest($conn);
    } elseif (isset($_POST['approve_id'])) {
        approveRequest($conn);
    } elseif (isset($_POST['reject_id'])) {
        rejectRequest($conn);
    } elseif (isset($_POST['pick_id'])) {
        pickupRequest($conn);
    } elseif (isset($_POST['logout'])) {
        logout();
    }

    header("Location: home_page.php");
    exit();
}

// Fetch all requests
$requests = $conn->query("SELECT * FROM requests ORDER BY request_date DESC")->fetchAll(PDO::FETCH_ASSOC);

// Close the connection
$conn = null;
?>





<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/static/style.css">
  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
      <style>
          .hidden {
              display: none;
          }
          .navbar-text {
              font-family: 'Great Vibes', cursive;
          }
        
      </style>
  <title>Test Dashboard</title>
</head>
<body class="bg-gray-50 dark:bg-gray-900 font-sans transition-colors duration-300">


<!-- Transparent Navbar -->
<div class="bg-transparent text-white px-6 py-4 shadow-md transition-all duration-500 ease-in-out transform" id="navbar-container">
  <div class="flex items-center justify-between">
    <!-- Website Name -->
    <div class="text-5xl font-semibold navbar-text text-indigo-600">
      <a href="#" class="hover:text-indigo-700">Mono Dev</a>
    </div>

    <!-- Hamburger Button for Small Screens -->
    <button id="menu-toggle" class="md:hidden text-gray-500 hover:text-indigo-700 focus:outline-none transition duration-300">
      <i id="menu-icon" class="fas fa-bars text-2xl transition-transform duration-300 ease-in-out"></i>
    </button>

    <!-- Right Side Content (Hidden on Small Screens) -->
    <div id="menu" class="hidden md:flex items-center space-x-6">
      <!-- User Info (Hidden on Small Screens) -->
      <div class="flex items-center space-x-4 hidden md:flex">
        <!-- User Avatar -->
        <button>
          <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="User Avatar" class="h-8 w-8 rounded-full border-2 border-gray-500 shadow-sm" />
        </button>
          <!-- User Role and Email -->
          <div class="text-sm text-gray-500">
              <span class="user-name text-blue-600 hover:text-blue-800 transition duration-300">
                  <?php echo htmlspecialchars($user['fullname']); ?>
              </span>
              <span class="user-role text-green-900 hover:text-green-900 transition duration-300 ml-2">
                  | <?php echo htmlspecialchars($role_display); ?>
              </span>
          </div>

        <!-- Logout Button -->
        <form method="POST" action="">
            <button type="submit" name="logout" class="text-indigo-500 hover:text-indigo-700 transition flex items-center space-x-2">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </form>

      </div>
    </div>
  </div>
</div>

<!-- Beautiful Floating Transparent SubNavbar -->
<div class="bg-transparent text-white px-6 py-4 shadow-lg rounded-2xl backdrop-blur-md transition-all duration-500 ease-in-out transform hover:shadow-2xl hover:-translate-y-1" id="navbar-container">
  <div class="flex items-center justify-between">
    <!-- Right Side Content (Hidden on Small Screens) -->
    <div id="menu" class="hidden md:flex items-center space-x-6">
      <!-- Navigation Links -->
      <nav class="hidden md:flex space-x-6 text-sm">
        <a href="home_page.php" class="text-indigo-700 font-semibold hover:text-indigo-500 transition duration-300 ease-in-out transform hover:scale-110 flex items-center gap-2">
          <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="home_page.php" class="text-indigo-700 hover:text-indigo-500 transition duration-300 ease-in-out transform hover:scale-110 flex items-center gap-2">
          <i class="fas fa-chart-bar"></i> Analytics
        </a>
        <a href="stock_control.php" class="text-indigo-700 hover:text-indigo-500 transition duration-300 ease-in-out transform hover:scale-110 flex items-center gap-2">
          <i class="fas fa-file-alt"></i> Reports
        </a>
      </nav>
    </div>
  </div>
</div>
<!-- Main Content -->
<div class="px-8 py-8">
  <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-8">Dashboard</h1>

  <!-- Dashboard Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mb-8">
    <div class="card bg-white dark:bg-gray-800 p-6 rounded-xl shadow-xl flex justify-between items-center transition-transform transform hover:-translate-y-2 hover:shadow-2xl duration-300">
      <div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-1">Total Requests</h3>
        <p class="text-4xl font-extrabold text-gray-700 dark:text-gray-200"><?php echo $totalRequests; ?></p>
      </div>
      <i class="fas fa-boxes text-5xl text-blue-500 animate-pulse"></i>
    </div>
    <div class="card bg-white dark:bg-gray-800 p-6 rounded-xl shadow-xl flex justify-between items-center transition-transform transform hover:-translate-y-2 hover:shadow-2xl duration-300">
      <div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-1">Approved Requests</h3>
        <p class="text-4xl font-extrabold text-green-700 dark:text-green-300"><?php echo $approvedRequests; ?></p>
      </div>
      <i class="fas fa-check-circle text-5xl text-green-500 animate-pulse"></i>
    </div>
    <div class="card bg-white dark:bg-gray-800 p-6 rounded-xl shadow-xl flex justify-between items-center transition-transform transform hover:-translate-y-2 hover:shadow-2xl duration-300">
      <div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-1">Pending Requests</h3>
        <p class="text-4xl font-extrabold text-yellow-600 dark:text-yellow-400"><?php echo $pendingRequests; ?></p>
      </div>
      <i class="fas fa-clock text-5xl text-yellow-500 animate-pulse"></i>
    </div>
  </div>

  <!-- Add Request Button -->
  <div class="flex justify-between items-center mb-8">
    <!-- Left-aligned button -->
    <button id="addRequestBtn" 
        class="bg-blue-500 text-white px-6 py-2 rounded-lg shadow-lg hover:bg-blue-600 transition duration-300 transform hover:scale-105">
        + Add Request
    </button>

    <!-- Right-aligned button with link to homepage -->
    <a href="home_page.php">
        <button id="approveRequestBtn" 
            class="bg-green-500 text-white px-6 py-2 rounded-lg shadow-lg hover:bg-green-600 transition duration-300 transform hover:scale-105">
            + Approved Requests
        </button>
    </a>
</div>




<!-- Redesigned Add Request Modal -->
<div id="addRequestModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
  <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md transform transition-all">
    <!-- Header -->
    <div class="flex justify-between items-center border-b pb-3 mb-4">
      <h2 class="text-xl font-bold text-gray-800">Add New Request</h2>
      <button id="cancelModalBtn" class="text-gray-500 hover:text-gray-700 transition">
        &times;
      </button>
    </div>
    
    <form method="POST" action="">
      <!-- Item Name Dropdown -->
      <div class="mb-4">
        <label for="item_name" class="block text-sm font-semibold text-gray-700 mb-1">Item Name</label>
        <select id="item_name" name="item_name" required class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500">
          <option value="" disabled selected>Select a stationery item</option>
          <option value="pen">Pen</option>
          <option value="notebook">Notebook</option>
          <option value="stapler">Stapler</option>
          <option value="scissors">Scissors</option>
          <option value="glue_stick">Glue Stick</option>
          <option value="marker">Marker</option>
        </select>
      </div>

      <!-- Quantity Input -->
      <div class="mb-4">
        <label for="quantity" class="block text-sm font-semibold text-gray-700 mb-1">Quantity</label>
        <input type="number" id="quantity" name="quantity" required class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      
      <!-- Buttons -->
      <div class="flex justify-end space-x-3">
        <button type="button" id="cancelModalBtn" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
          Cancel
        </button>
        <button type="submit" name="add_request" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
          Add
        </button>
      </div>
    </form>
  </div>
</div>
<!-- Responsive Requests Table with Sorting and Pagination -->
<div class="overflow-x-auto">
  <div class="relative shadow-lg rounded-lg overflow-hidden">
    <table class="w-full bg-white border-collapse rounded-lg">
      <thead class="bg-gradient-to-r from-green-200 to-blue-300 text-blue text-center">
        <tr>
          <th class="px-6 py-4 text-left text-sm font-medium uppercase cursor-pointer" onclick="sortTable(0)">
            ID <i class="fas fa-sort"></i>
          </th>
          <th class="px-6 py-4 text-left text-sm font-medium uppercase cursor-pointer" onclick="sortTable(1)">
            Item Name <i class="fas fa-sort"></i>
          </th>
          <th class="px-6 py-4 text-left text-sm font-medium uppercase cursor-pointer" onclick="sortTable(2)">
            Quantity <i class="fas fa-sort"></i>
          </th>
          <th class="px-6 py-4 text-left text-sm font-medium uppercase cursor-pointer" onclick="sortTable(3)">
            Request Date <i class="fas fa-sort"></i>
          </th>
          <th class="px-6 py-4 text-left text-sm font-medium uppercase cursor-pointer" onclick="sortTable(4)">
            Status <i class="fas fa-sort"></i>
          </th>
          <th class="px-6 py-4 text-left text-sm font-medium uppercase">Actions</th>
        </tr>
      </thead>
      <tbody id="table-body" class="divide-y divide-gray-200">
        <?php if (empty($requests)): ?>
          <tr>
            <td colspan="6" class="px-6 py-6 text-center text-gray-500">No requests found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($requests as $request): ?>
            <tr class="hover:bg-gray-100 transition" data-id="<?php echo htmlspecialchars($request['id']); ?>">
              <td class="px-6 py-4 text-gray-700"><?php echo htmlspecialchars($request['id']); ?></td>
              <td class="px-6 py-4 text-gray-700"><?php echo htmlspecialchars($request['item_name']); ?></td>
              <td class="px-6 py-4 text-gray-700"><?php echo htmlspecialchars($request['quantity']); ?></td>
              <td class="px-6 py-4 text-gray-700"><?php echo htmlspecialchars($request['request_date']); ?></td>
              <td class="px-6 py-4 font-semibold 
                  <?php 
                      if ($request['status'] == 'Approved') { 
                          echo 'text-green-600'; 
                      } elseif ($request['status'] == 'Pending') { 
                          echo 'text-yellow-600'; 
                      } elseif ($request['status'] == 'Rejected') { 
                          echo 'text-red-600'; 
                      } else { 
                          echo 'text-blue-600'; // Default color
                      } 
                  ?>">
                  <?php echo htmlspecialchars($request['status']); ?>
              </td>
              <td class="px-6 py-4 flex space-x-3">
    <?php
    if (isset($_SESSION['user_role'])) {
        $permissions = [
            100 => ['approve', 'reject', 'pick', 'delete'], // Admin: All buttons
            111 => ['delete'], // Specific role (not listed before)
            222 => ['approve', 'reject'], // Officer: Access denied
            333 => ['approve', 'reject', 'pick'], // Branch Manager
            444 => ['approve', 'reject'], // Stock Controller
            555 => ['approve', 'reject', 'delete'] // Admin Manager
        ];

        $buttons = [
            'approve' => '<button type="submit" class="text-green-600 hover:text-green-800" title="Approve"><i class="fas fa-check-circle"></i></button>',
            'reject' => '<button type="submit" class="text-red-600 hover:text-red-800" title="Reject"><i class="fas fa-times-circle"></i></button>',
            'pick' => '<button type="submit" class="text-blue-600 hover:text-blue-800" title="Pick Up"><i class="fas fa-car"></i></button>',
            'delete' => '<button type="submit" class="text-red-600 hover:text-red-800" title="Delete"><i class="fas fa-trash"></i></button>',
            'access' => '<i class="text-red-600 hover:text-red-800" title="Access denied">Access denied</i>'
        ];

        foreach ($permissions[$_SESSION['user_role']] as $action) {
            echo '<form method="POST" action="home_page.php">
                    <input type="hidden" name="' . $action . '_id" value="' . htmlspecialchars($request['id']) . '">' 
                    . $buttons[$action] . 
                  '</form>';
        }
    }
    ?>
</td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  
<!-- Pagination -->
<div class="flex justify-center items-center mt-6 space-x-6">
  <button id="prevPage" class="px-5 py-3 rounded-full bg-gradient-to-r from-purple-500 to-blue-500 text-white shadow-lg transform transition-all duration-300 hover:scale-110 hover:shadow-2xl hover:from-blue-500 hover:to-purple-500">
    <i class="fas fa-chevron-left"></i>
  </button>
  <span id="pageInfo" class="text-lg font-bold text-gray-800 bg-gray-200 px-4 py-2 rounded-lg shadow-md transition-all duration-300 hover:bg-gray-300">
    Page 1 of X
  </span>
  <button id="nextPage" class="px-5 py-3 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 text-white shadow-lg transform transition-all duration-300 hover:scale-110 hover:shadow-2xl hover:from-purple-500 hover:to-blue-500">
    <i class="fas fa-chevron-right"></i>
  </button>
</div><br>
</div>

<script>
  let currentPage = 1;
  const rowsPerPage = 5;
  const tableBody = document.getElementById("table-body");
  const rows = tableBody.querySelectorAll("tr");
  const totalPages = Math.ceil(rows.length / rowsPerPage);
  
  function updateTable() {
    rows.forEach((row, index) => {
      row.style.display = (index >= (currentPage - 1) * rowsPerPage && index < currentPage * rowsPerPage) ? "" : "none";
    });
    document.getElementById("pageInfo").innerText = `Page ${currentPage} of ${totalPages}`;
  }
  
  document.getElementById("prevPage").addEventListener("click", () => {
    if (currentPage > 1) {
      currentPage--;
      updateTable();
    }
  });
  
  document.getElementById("nextPage").addEventListener("click", () => {
    if (currentPage < totalPages) {
      currentPage++;
      updateTable();
    }
  });
  
  function sortTable(columnIndex) {
    const table = document.querySelector("table");
    const tbody = table.querySelector("tbody");
    const rowsArray = Array.from(tbody.rows);
    const isAscending = table.dataset.sortOrder === "asc";
    
    rowsArray.sort((a, b) => {
      const cellA = a.cells[columnIndex].innerText.trim();
      const cellB = b.cells[columnIndex].innerText.trim();
      return isAscending ? cellA.localeCompare(cellB, undefined, { numeric: true }) : cellB.localeCompare(cellA, undefined, { numeric: true });
    });
    
    rowsArray.forEach(row => tbody.appendChild(row));
    table.dataset.sortOrder = isAscending ? "desc" : "asc";
  }
  
  updateTable();
</script>
<script>
  // Modal functionality
  const addRequestBtn = document.getElementById('addRequestBtn');
  const addRequestModal = document.getElementById('addRequestModal');
  const cancelModalBtn = document.getElementById('cancelModalBtn');

  addRequestBtn.addEventListener('click', () => {
    addRequestModal.classList.remove('hidden');
  });

  cancelModalBtn.addEventListener('click', () => {
    addRequestModal.classList.add('hidden');
  });
  
</script>
</body>
</html>