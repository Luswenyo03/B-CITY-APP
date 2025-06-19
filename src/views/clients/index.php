<?php
// Ensure $clients and $contacts variables are defined as arrays to avoid errors
$clients = $clients ?? [];
$contacts = $contacts ?? [];
?>

<!-- Hidden message container for displaying any info/status messages -->
<div id="header-message" class="info-message" style="display: none;"></div>

<!-- Page heading -->
<h2 id="pageTitle">Clients List</h2>

<!-- If there is a message passed from backend, display it safely -->
<?php if (!empty($message)): ?>
    <div class="info-message"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<!-- Container for the list of clients -->
<div id="clientListContainer">

    <!-- If no clients found, show a friendly message -->
    <?php if (empty($clients)): ?>
        <p>No client(s) found</p>
    <?php else: ?>
        <!-- Table listing all clients -->
        <table>
            <tr>
                <th>Name</th>
                <th>Client Code</th>
                <th class="center">No. of Linked Contacts</th>
                <th>Action</th>
            </tr>

            <!-- Loop through each client and display their info in a row -->
            <?php foreach ($clients as $client): ?>
                <tr class="client-row"
                    data-client-id="<?php echo $client['id']; ?>"
                    data-client-name="<?php echo htmlspecialchars($client['name']); ?>"
                    data-client-code="<?php echo htmlspecialchars($client['client_code']); ?>">
                    <!-- Client Name -->
                    <td><?php echo htmlspecialchars($client['name']); ?></td>
                    <!-- Client Code -->
                    <td><?php echo htmlspecialchars($client['client_code']); ?></td>
                    <!-- Number of contacts linked to this client -->
                    <td class="center"><?php echo $client['contact_count']; ?></td>
                    <td>
                        <!-- Link to toggle the contact linking form for this client -->
                        <a href="#" onclick="toggleLinkContacts(<?php echo $client['id']; ?>); event.stopPropagation(); return false;">Link Contacts</a>
                        |
                        <!-- Button to view detailed client info -->
                        <button type="button" class="view-client-btn"
                            data-client-id="<?php echo $client['id']; ?>"
                            data-client-name="<?php echo htmlspecialchars($client['name']); ?>"
                            data-client-code="<?php echo htmlspecialchars($client['client_code']); ?>">
                            View
                        </button>
                    </td>
                </tr>

                <!-- Hidden form row for linking contacts to the client -->
                <tr id="linkContactsRow_<?php echo $client['id']; ?>" class="hidden">
                    <td colspan="4">
                        <form method="POST" action="?controller=client&action=linkContacts">
                            <!-- Hidden input to specify which client we are linking contacts to -->
                            <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                            <label>Select contacts to link:</label><br>
                            <select name="contact_ids[]" multiple size="5" style="width: 100%;">
                                <!-- List all contacts as options for linking -->
                                <?php foreach ($contacts as $contact): ?>
                                    <option value="<?php echo $contact['id']; ?>">
                                        <?php 
                                            // Display contact surname, name and email safely escaped
                                            echo htmlspecialchars($contact['surname']) . ' ' . 
                                                 htmlspecialchars($contact['name']) . ' - ' . 
                                                 htmlspecialchars($contact['email']); 
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <br>
                            <!-- Button to submit selected contacts for linking -->
                            <button type="submit">Link Selected Contacts</button>
                            <!-- Button to cancel and hide the linking form -->
                            <button type="button" onclick="toggleLinkContacts(<?php echo $client['id']; ?>)">Cancel</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- Link to create a new client -->
    <a href="?controller=client&action=create">Create New Client</a>
</div>

<!-- Detailed Client Info section (hidden by default) -->
<div id="clientDetails" class="hidden" style="margin-top: 30px;">
    <!-- Button to return back to the clients list -->
    <button id="backToListBtn" style="margin-bottom: 10px;">&larr; Back to Clients List</button>

    <h3>Client Details</h3>

    <!-- Tabs for toggling between General info and linked Contacts -->
    <div>
        <button class="tablink" onclick="openTab(event, 'GeneralTab')">General</button>
        <button class="tablink" onclick="openTab(event, 'ContactTab')">Contacts</button>
    </div>

    <!-- General tab content showing client name and code -->
    <div id="GeneralTab" class="tab" style="display: none; margin-top:10px;">
        <label>Name: <input type="text" id="detailName" readonly></label><br>
        <label>Client Code: <input type="text" id="detailCode" readonly></label><br>
    </div>

    <!-- Contacts tab content showing linked contacts in a table -->
    <div id="ContactTab" class="tab" style="display: none; margin-top:10px;">
        <table border="1" cellpadding="5" style="width:100%;">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="contactList"></tbody> <!-- Populated dynamically -->
        </table>
    </div>
</div>

<script>
// Toggles the display of the contact linking form for a given client
function toggleLinkContacts(clientId) {
    const row = document.getElementById('linkContactsRow_' + clientId);
    if (row) {
        // Toggle between showing and hiding the row
        row.style.display = row.style.display === 'table-row' ? 'none' : 'table-row';
    }
}

// Switches visible tab content and highlights the active tab button
function openTab(evt, tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab').forEach(tab => {
        tab.style.display = 'none';
    });

    // Show the selected tab content
    document.getElementById(tabName).style.display = 'block';

    // Remove 'active' class from all tab buttons
    document.querySelectorAll('.tablink').forEach(btn => {
        btn.classList.remove('active');
    });

    // Add 'active' class to the clicked tab button
    evt.currentTarget.classList.add('active');
}

// Fetches and shows detailed client info and linked contacts in the details section
function showClientDetails(clientId, name, code) {
    // Hide the main client list and create link
    document.getElementById('clientListContainer').style.display = 'none';
    document.querySelector('a[href="?controller=client&action=create"]').style.display = 'none';

    // Show the client details section
    const details = document.getElementById('clientDetails');
    details.classList.remove('hidden');

    // Set client name and code fields in the General tab
    document.getElementById('detailName').value = name;
    document.getElementById('detailCode').value = code;

    // Fetch linked contacts for this client via AJAX
    fetch(`?controller=client&action=getLinkedContacts&client_id=${clientId}`)
        .then(res => res.json())
        .then(data => {
            const contactList = document.getElementById('contactList');
            contactList.innerHTML = '';

            if (data.length) {
                // Populate the linked contacts table rows
                data.forEach(contact => {
                    contactList.innerHTML += `
                        <tr>
                            <td style="text-align:left;">${contact.surname} ${contact.name}</td>
                            <td>${contact.email}</td>
                            <td>
                              <a href="#" class="unlink-contact" 
                                 data-client-id="${clientId}" 
                                 data-contact-id="${contact.id}">Unlink</a>
                            </td>
                        </tr>
                    `;
                });
            } else {
                // Show message if no linked contacts
                contactList.innerHTML = '<tr><td colspan="3">No contacts linked.</td></tr>';
            }
            // Bind click handlers to unlink links dynamically added
            bindUnlinkLinks();
        });
}

// Adds event listeners to all unlink contact links to handle unlink requests
function bindUnlinkLinks() {
    document.querySelectorAll('.unlink-contact').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            const clientId = this.getAttribute('data-client-id');
            const contactId = this.getAttribute('data-contact-id');

            // Send POST request to unlink the contact from client
            fetch('?controller=client&action=unlinkContact', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `client_id=${clientId}&contact_id=${contactId}`
            })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    // Refresh client details to reflect updated linked contacts
                    showClientDetails(clientId);
                } else {
                    alert('Failed to unlink contact.');
                }
            });
        });
    });
}

// Setup event listeners once the DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
    // Add click handlers to all "View" buttons for clients
    document.querySelectorAll('.view-client-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const clientId = btn.getAttribute('data-client-id');
            const name = btn.getAttribute('data-client-name');
            const code = btn.getAttribute('data-client-code');

            // Show client details and open the General tab by default
            showClientDetails(clientId, name, code);
            openTab({ currentTarget: document.querySelector('.tablink') }, 'GeneralTab');
        });
    });

    // Back button returns to clients list view
    document.getElementById('backToListBtn').addEventListener('click', () => {
        document.getElementById('clientDetails').classList.add('hidden');
        document.getElementById('clientListContainer').style.display = '';
        document.querySelector('a[href="?controller=client&action=create"]').style.display = '';
        document.getElementById('pageTitle').textContent = 'Clients List';
    });
});
</script>
