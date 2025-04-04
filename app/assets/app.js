// import "./bootstrap.js";
require("./styles/app.css");

// Start with a simpler configuration
// console.log("Welcome to Dawn GN!");

// Classes de boutons globales
window.BUTTON_CLASSES = {
  primary:
    "border-0 inline-flex justify-center rounded-md bg-gradient-to-tl from-gray-900 to-slate-800 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:scale-102 hover:bg-slate-800 w-30",
  secondary:
    "border-0 inline-flex justify-center rounded-md bg-gradient-to-tl from-slate-600 to-slate-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:scale-102 hover:opacity-75 w-30",
  danger:
    "border-0 inline-flex justify-center rounded-md bg-gradient-to-tl from-red-600 to-rose-400 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:scale-102 hover:opacity-75 w-40",
  link: {
    edit: "inline-block pl-0 pr-4 mb-0 ml-auto font-bold text-center align-middle transition-all bg-transparent border-0 rounded-lg shadow-none cursor-pointer leading-pro text-xs ease-soft-in text-slate-400 hover:text-slate-700 hover:underline",
    delete:
      "inline-block pl-4 mb-0 ml-0 font-bold text-center align-middle transition-all bg-transparent border-0 shadow-none cursor-pointer leading-pro text-xs ease-soft-in text-slate-400 hover:text-red-600 hover:underline",
  },
};

// Classes des modales utilisant les classes de boutons globales
window.modalClasses = {
  confirmButton: window.BUTTON_CLASSES.primary,
  cancelButton: window.BUTTON_CLASSES.secondary,
  actions: "flex gap-4 justify-center mt-4",
  popup: "w-96",
};

window.modalDeleteClasses = {
  confirmButton: window.BUTTON_CLASSES.danger,
  cancelButton: window.BUTTON_CLASSES.secondary,
  actions: "flex gap-4 justify-center mt-4",
  popup: "w-96",
};

// Tickets function
window.sweetTicket = function () {
  Swal.fire({
    customClass: {
      text: "!mt-2 sm:!mt-0 !m-0 !text-center sm:!text-left !text-s !text-gray-500 !pl-4 sm:!pl-0 !pr-4 !pb-4 sm:!pr-6 sm:!pb-4 sm:!ml-4 !col-start-1 sm:!col-start-2 !col-end-3",
      confirmButton: window.BUTTON_CLASSES.primary,
    },
    width: "50%",
    text: "Vous le trouverez ici, sur le billet HelloAsso reçu par mail.",
    imageUrl: "/build/images/billet_info.jpg",
    imageWidth: "100%",
    confirmButtonText: "J'ai compris !",
    animation: false,
  });
};

window.showContactForm = function () {
  createModal({
    title: "Contacter mon orga",
    html: `
            <form id="contactForm" class="text-left">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Sujet</label>
                    <input type="text" id="subject" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Message</label>
                    <textarea id="message" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500"></textarea>
                </div>
            </form>
        `,
    getData: () => ({
      subject: document.getElementById("subject").value,
      message: document.getElementById("message").value,
    }),
    validate: (data) => data.subject && data.message,
    url: "/api/contact", // Ajoutez l'URL appropriée
    confirmButtonText: "Envoyer",
    successTitle: "Message envoyé !",
    successMessage: "Votre message a bien été transmis à votre orga.",
  });
};

// Fonction globale de confirmation de suppression
window.confirmDelete = function (url) {
  createModal({
    title: "Êtes-vous sûr ?",
    html: "Cette action ne peut pas être annulée !",
    confirmButtonText: "Oui, supprimer !",
    customClass: window.modalDeleteClasses,
    getData: () => ({}),
    validate: () => true,
    url: url,
    successTitle: "Supprimé !",
    successMessage: "L'élément a été supprimé avec succès.",
  });
};

// Fonction globale pour afficher une alerte de succès
window.showSuccessAlert = function (title, text) {
  return Swal.fire({
    title: title,
    text: text,
    icon: "success",
    customClass: window.modalClasses,
  }).then(() => {
    window.location.reload();
  });
};

// Fonction globale pour afficher une alerte d'erreur
window.showErrorAlert = function (error) {
  return Swal.fire({
    title: "Erreur !",
    text: error.message,
    icon: "error",
    customClass: window.modalClasses,
  });
};

// Fonction générique pour créer une modale
window.createModal = function (options) {
  return Swal.fire({
    title: options.title,
    html: options.html,
    showCancelButton: true,
    confirmButtonText: options.confirmButtonText || "Ajouter",
    cancelButtonText: "Annuler",
    customClass: options.customClass || window.modalClasses,
    didOpen: options.didOpen,
    preConfirm: () => {
      const data = options.getData();

      if (!options.validate(data)) {
        Swal.showValidationMessage("Veuillez remplir tous les champs");
        return false;
      }

      return fetch(options.url, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      })
        .then((response) =>
          response.json().then((data) => {
            if (!response.ok) {
              throw new Error(data.error || "Une erreur est survenue");
            }
            return data;
          })
        )
        .catch((error) => {
          Swal.showValidationMessage(error.message);
        });
    },
  }).then((result) => {
    if (result.isConfirmed) {
      showSuccessAlert(options.successTitle, options.successMessage);
    }
  });
};
