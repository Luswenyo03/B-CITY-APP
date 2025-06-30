function validateForm(event, formId) {
  event.preventDefault();
  const form = document.getElementById(formId);
  const inputs = form.getElementsByTagName("input");
  let isValid = true;
  for (let input of inputs) {
    if (input.required && !input.value.trim()) {
      alert(`${input.name} is required`);
      isValid = false;
    }
    if (
      input.type === "email" &&
      input.value &&
      !/^\S+@\S+\.\S+$/.test(input.value)
    ) {
      alert("Please enter a valid email");
      isValid = false;
    }
  }
  if (isValid) form.submit();
}
function unlinkItem(type, clientId, contactId) {
  console.log(
    `unlinkItem called: type=${type}, clientId=${clientId}, contactId=${contactId}`
  );
  if (!confirm("Are you sure you want to unlink this contact?")) {
    return;
  }
  fetch("?controller=client&action=unlink", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `client_id=${encodeURIComponent(
      clientId
    )}&contact_id=${encodeURIComponent(contactId)}`,
  })
    .then((response) => {
      console.log(`Response status: ${response.status}`);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      console.log(`Response data:`, data);
      if (data.success) {
        // Reload page to ensure UI and database are in sync
        window.location.href = "?controller=client&action=index";
      } else {
        alert(`Failed to unlink contact: ${data.message || "Unknown error"}`);
      }
    })
    .catch((err) => {
      console.error("Fetch error:", err);
      alert("Error occurred while unlinking contact.");
    });
}

function openTab(evt, tabName, formId) {
  const tabcontent = document.getElementsByClassName("tab");
  for (let i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  const tablinks = document.getElementsByClassName("tablink");
  for (let i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active";
  if (formId) document.getElementById(formId).dataset.activeTab = tabName;
}

window.onload = function () {
  const forms = document.getElementsByTagName("form");
  for (let form of forms) {
    const defaultTab = form.getElementsByClassName("tab")[0].id;
    openTab(
      { currentTarget: form.getElementsByClassName("tablink")[0] },
      defaultTab,
      form.id
    );
  }
};

document.getElementById("name").addEventListener("input", function () {
  const name = this.value.trim();

  if (name.length >= 2) {
    fetch(
      `?controller=client&action=generateClientCode&name=${encodeURIComponent(
        name
      )}`
    )
      .then((res) => res.json())
      .then((data) => {
        document.getElementById("client_code").value = data.client_code || "";
      })
      .catch(() => {
        document.getElementById("client_code").value = "";
      });
  } else {
    document.getElementById("client_code").value = "";
  }
});
