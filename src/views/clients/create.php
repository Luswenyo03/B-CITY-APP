<?php $title = "Create New Client"; ?>

<h2>Create New Client</h2>
<form id="clientForm" method="POST" onsubmit="validateForm(event, 'clientForm')">
    <div id="General" class="tab">
        <div>
            <label>Name: <input type="text" name="name"></label>
            
        </div>
        
    </div>
    <?php if (isset($error)): ?>
    <div class="error-message"><?php echo $error; ?></div>
<?php endif; ?>

    <button type="submit">Submit</button>
</form>
