<?php $title = "Create New Contact"; ?>

<h2>Create New Contact</h2>
<form id="contactForm" method="POST" onsubmit="validateForm(event, 'contactForm')">
    <div>
        <button type="button" class="tablink" onclick="openTab(event, 'General', 'contactForm')">General</button>
        <button type="button" class="tablink" onclick="openTab(event, 'Clients', 'contactForm')">Clients</button>
    </div>

    <div id="General" class="tab">
        <div>
            <label>Name: <input type="text" name="name"></label>
        </div>
        <div>
            <label>Email: <input type="email" name="email"></label>
        </div>
    </div>

    <div id="Clients" class="tab">
        <div>
            <label>Link to Clients:
                <select name="client_ids[]" multiple>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?php echo $client['id']; ?>">
                            <?php echo htmlspecialchars($client['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
    </div>

    <?php if (isset($error)): ?>
    <div class="error-message"><?php echo $error; ?></div>
<?php endif; ?>


    <button type="submit">Submit</button>
</form>
