/*!

=========================================================
* Soft UI Dashboard Tailwind - v1.0.5
=========================================================

* Product Page: https://www.creative-tim.com/product/soft-ui-dashboard-tailwind
* Copyright 2023 Creative Tim (https://www.creative-tim.com)
* Licensed under MIT (site.license)

* Coded by www.creative-tim.com

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

*/
var to_build = "/";

loadStylesheet(to_build + "build/styles/perfect-scrollbar.css");
loadJS(to_build + "build/js/perfect-scrollbar.js", true);

if (document.querySelector("nav [navbar-trigger]")) {
  loadJS(to_build + "build/js/navbar-collapse.js", true);
}

if (document.querySelector("[data-target='tooltip']")) {
  loadJS(to_build + "build/js/tooltips.js", true);
  loadStylesheet(to_build + "build/styles/tooltips.css");
}

if (document.querySelector("[nav-pills]")) {
  loadJS(to_build + "build/js/nav-pills.js", true);
}

if (document.querySelector("[dropdown-trigger]")) {
  loadJS(to_build + "build/js/dropdown.js", true);
}

if (document.querySelector("[fixed-plugin]")) {
  loadJS(to_build + "build/js/fixed-plugin.js", true);
}

if (document.querySelector("[navbar-main]")) {
  loadJS(to_build + "build/js/sidenav-burger.js", true);
}

if (document.querySelector("canvas")) {
  loadJS(to_build + "build/js/chart-1.js", true);
  loadJS(to_build + "build/js/chart-2.js", true);
}

function loadJS(FILE_URL, async) {
  let dynamicScript = document.createElement("script");

  dynamicScript.setAttribute("src", FILE_URL);
  dynamicScript.setAttribute("type", "text/javascript");
  dynamicScript.setAttribute("async", async);

  document.head.appendChild(dynamicScript);
}

function loadStylesheet(FILE_URL) {
  let dynamicStylesheet = document.createElement("link");

  dynamicStylesheet.setAttribute("href", FILE_URL);
  dynamicStylesheet.setAttribute("type", "text/css");
  dynamicStylesheet.setAttribute("rel", "stylesheet");

  document.head.appendChild(dynamicStylesheet);
}

// Initialization of Tooltips
var tooltipTriggerList = [].slice.call(
  document.querySelectorAll('[data-bs-toggle="tooltip"]')
);
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl);
});

// Fixed Plugin
if (document.querySelector(".fixed-plugin")) {
  var fixedPlugin = document.querySelector(".fixed-plugin");
  var fixedPluginButton = document.querySelector(".fixed-plugin-button");
  var fixedPluginButtonNav = document.querySelector(".fixed-plugin-button-nav");
  var fixedPluginCard = document.querySelector(".fixed-plugin .card");
  var fixedPluginCloseButton = document.querySelector(
    ".fixed-plugin-close-button"
  );

  var navbar = document.querySelector("#navbarBlur");
  var buttonNavbarFixed = document.querySelector("#navbarFixed");

  if (fixedPluginButton) {
    fixedPluginButton.onclick = function () {
      if (!fixedPlugin.classList.contains("show")) {
        fixedPlugin.classList.add("show");
      } else {
        fixedPlugin.classList.remove("show");
      }
    };
  }

  if (fixedPluginButtonNav) {
    fixedPluginButtonNav.onclick = function () {
      if (!fixedPlugin.classList.contains("show")) {
        fixedPlugin.classList.add("show");
      } else {
        fixedPlugin.classList.remove("show");
      }
    };
  }

  fixedPluginCloseButton.onclick = function () {
    fixedPlugin.classList.remove("show");
  };
}

// Sidenav
// var sidenav = document.querySelector("aside");
// var sidenav_trigger = document.querySelector("[sidenav-trigger]");
// var sidenav_close_button = document.querySelector("[sidenav-close]");

// if (sidenav_trigger) {
//   sidenav_trigger.addEventListener("click", function () {
//     sidenav.classList.toggle("-translate-x-full");
//   });
// }

// if (sidenav_close_button) {
//   sidenav_close_button.addEventListener("click", function () {
//     sidenav.classList.add("-translate-x-full");
//   });
// }

// // Function for the fixed navbar
// function navbar_fixed_plugin() {
//   if (buttonNavbarFixed.getAttribute("checked") === "true") {
//     buttonNavbarFixed.setAttribute("checked", "false");
//     navbar.classList.remove("sticky");
//     navbar.classList.remove("top-[1%]");
//     navbar.classList.add("relative");
//   } else {
//     buttonNavbarFixed.setAttribute("checked", "true");
//     navbar.classList.remove("relative");
//     navbar.classList.add("sticky");
//     navbar.classList.add("top-[1%]");
//   }
// }
