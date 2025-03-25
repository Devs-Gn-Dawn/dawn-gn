// import "./bootstrap.js";
require("./styles/app.css");

// Start with a simpler configuration
// console.log("Welcome to Dawn GN!");

// Tickets function
function sweetTicket() {
  Swal.fire({
    customClass: {
      text: "!mt-2 sm:!mt-0 !m-0 !text-center sm:!text-left !text-s !text-gray-500 !pl-4 sm:!pl-0 !pr-4 !pb-4 sm:!pr-6 sm:!pb-4 sm:!ml-4 !col-start-1 sm:!col-start-2 !col-end-3",
      confirmButton:
        "border-0 inline-flex w-full justify-center rounded-md bg-gradient-to-tl from-gray-900 to-slate-800 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:scale-102 hover:bg-slate-800 sm:ml-3 sm:w-auto",
    },
    width: "50%",
    text: "Vous le trouverez ici, sur le billet HelloAsso reçu par mail.",
    imageUrl: "{{ asset('build/images/billet_info.jpg') }}",
    imageWidth: "100%",
    confirmButtonText: "J'ai compris !",
    animation: false,
  });
}

window.showContactForm = function () {
  Swal.fire({
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
    showCancelButton: true,
    confirmButtonText: "Envoyer",
    cancelButtonText: "Annuler",
    customClass: {
      confirmButton:
        "bg-gradient-to-tl from-gray-900 to-slate-800 text-white px-4 py-2 rounded-md hover:scale-102",
      cancelButton:
        "border border-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-50",
    },
    preConfirm: () => {
      const subject = document.getElementById("subject").value;
      const message = document.getElementById("message").value;

      if (!subject || !message) {
        Swal.showValidationMessage("Veuillez remplir tous les champs");
        return false;
      }

      // Ici vous pouvez ajouter la logique d'envoi du formulaire
      return { subject, message };
    },
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: "Message envoyé !",
        text: "Votre message a bien été transmis à votre orga.",
        icon: "success",
        customClass: {
          confirmButton:
            "bg-gradient-to-tl from-gray-900 to-slate-800 text-white px-4 py-2 rounded-md hover:scale-102",
        },
      });
    }
  });
};
