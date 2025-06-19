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

function toggleLinkContacts(clientId) {
  const row = document.getElementById("linkContactsRow_" + clientId);
  if (row) {
    row.style.display =
      row.style.display === "table-row" ? "none" : "table-row";
  }
}

function openTab(evt, tabName) {
  document.querySelectorAll(".tab").forEach((tab) => {
    tab.style.display = "none";
  });
  document.getElementById(tabName).style.display = "block";

  document.querySelectorAll(".tablink").forEach((btn) => {
    btn.classList.remove("active");
  });
  evt.currentTarget.classList.add("active");
}

function showClientDetails(clientId, name, code) {
  document.getElementById("clientListContainer").style.display = "none";
  document.querySelector(
    'a[href="?controller=client&action=create"]'
  ).style.display = "none";

  const details = document.getElementById("clientDetails");
  details.classList.remove("hidden");

  document.getElementById("detailName").value = name;
  document.getElementById("detailCode").value = code;

  fetch(`?controller=client&action=getLinkedContacts&client_id=${clientId}`)
    .then((res) => res.json())
    .then((data) => {
      const contactList = document.getElementById("contactList");
      contactList.innerHTML = "";
      if (data.length) {
        data.forEach((contact) => {
          contactList.innerHTML += `
                      <tr>
                          <td style="text-align:left;">${contact.surname} ${contact.name}</td>
                          <td>${contact.email}</td>
                          <td><a href="#" class="unlink-contact" data-client-id="${clientId}" data-contact-id="${contact.id}">Unlink</a></td>
                      </tr>
                  `;
        });
      } else {
        contactList.innerHTML =
          '<tr><td colspan="3">No contacts linked.</td></tr>';
      }
      bindUnlinkLinks();
    });
}

document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".view-client-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      const clientId = btn.getAttribute("data-client-id");
      const name = btn.getAttribute("data-client-name");
      const code = btn.getAttribute("data-client-code");
      showClientDetails(clientId, name, code);
      openTab(
        { currentTarget: document.querySelector(".tablink") },
        "GeneralTab"
      );
    });
  });

  document.getElementById("backToListBtn").addEventListener("click", () => {
    document.getElementById("clientDetails").classList.add("hidden");
    document.getElementById("clientListContainer").style.display = "";
    document.querySelector(
      'a[href="?controller=client&action=create"]'
    ).style.display = "";
    document.getElementById("pageTitle").textContent = "Clients List";
  });
});

function toggleLinkClients(contactId) {
  const row = document.getElementById("linkClientsRow_" + contactId);
  if (row) {
    row.style.display =
      row.style.display === "table-row" ? "none" : "table-row";
  }
}

function openTab(evt, tabName) {
  document
    .querySelectorAll(".tab")
    .forEach((tab) => (tab.style.display = "none"));
  document.getElementById(tabName).style.display = "block";

  document
    .querySelectorAll(".tablink")
    .forEach((btn) => btn.classList.remove("active"));
  evt.currentTarget.classList.add("active");
}

function showContactDetails(contactId, name, surname, email) {
  document.getElementById("contactListContainer").style.display = "none";
  document.querySelector(
    'a[href="?controller=contact&action=create"]'
  ).style.display = "none";

  const details = document.getElementById("contactDetails");
  details.classList.remove("hidden");

  fetch(`?controller=contact&action=getLinkedClients&contact_id=${contactId}`)
    .then((res) => res.json())
    .then((data) => {
      const contact = data.contact;
      const clients = data.clients;

      document.getElementById("detailSurname").value = contact.surname;
      document.getElementById("detailName").value = contact.name;
      document.getElementById("detailEmail").value = contact.email;

      const clientList = document.getElementById("linkedClientsList");
      clientList.innerHTML = "";
      if (clients.length) {
        clients.forEach((client) => {
          clientList.innerHTML += `
                      <tr>
                          <td>${client.name}</td>
                          <td>${client.client_code}</td>
                          <td><a href="#" class="unlink-client" data-contact-id="${contactId}" data-client-id="${client.id}">Unlink</a></td>
                      </tr>
                  `;
        });
        bindUnlinkClientLinks();
      } else {
        clientList.innerHTML =
          '<tr><td colspan="3">No clients linked.</td></tr>';
      }
    });
}

function bindUnlinkClientLinks() {
  document.querySelectorAll(".unlink-client").forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const contactId = this.getAttribute("data-contact-id");
      const clientId = this.getAttribute("data-client-id");

      fetch("?controller=contact&action=unlinkContact", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `contact_id=${contactId}&client_id=${clientId}`,
      })
        .then((res) => res.json())
        .then((response) => {
          if (response.success) {
            showContactDetails(contactId);
          } else {
            alert("Failed to unlink client.");
          }
        });
    });
  });
}

document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".view-contact-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      const contactId = btn.getAttribute("data-contact-id");
      const name = btn.getAttribute("data-contact-name");
      const surname = btn.getAttribute("data-contact-surname");
      const email = btn
        .closest("tr")
        .querySelector("td:nth-child(2)").textContent;

      showContactDetails(contactId, name, surname, email);
      openTab(
        { currentTarget: document.querySelector(".tablink") },
        "ContactGeneralTab"
      );
    });
  });

  document
    .getElementById("backToContactListBtn")
    .addEventListener("click", () => {
      document.getElementById("contactDetails").classList.add("hidden");
      document.getElementById("contactListContainer").style.display = "";
      document.querySelector(
        'a[href="?controller=contact&action=create"]'
      ).style.display = "";
      document.getElementById("pageTitle").textContent = "Contacts List";
    });
});
