document.addEventListener("DOMContentLoaded", function () {
  // ── Modal ──
  const noteModal = document.getElementById("noteModal");
  if (noteModal) {
    noteModal.addEventListener("show.bs.modal", function (e) {
      const trigger = e.relatedTarget;
      document.getElementById("noteModalContent").style.backgroundColor =
        trigger.getAttribute("data-color");
      document.getElementById("noteModalText").textContent =
        trigger.getAttribute("data-content");
      document.getElementById("noteModalAuthor").textContent =
        trigger.getAttribute("data-author");
      document.getElementById("noteModalDate").textContent =
        trigger.getAttribute("data-date");
    });
  }

  // ── Categories toggle ──
  const toggle = document.getElementById("hasCategories");
  const section = document.getElementById("categoriesSection");

  function syncCategoriesVisibility() {
    if (section)
      section.style.display = toggle && toggle.checked ? "block" : "none";
  }

  if (toggle) {
    toggle.addEventListener("change", syncCategoriesVisibility);
    syncCategoriesVisibility();
  }

  // ── Add category row ──
  document
    .getElementById("addCategory")
    ?.addEventListener("click", function () {
      const row = document.createElement("div");
      row.className = "category-row d-flex align-items-center gap-2";
      row.innerHTML = `
          <input type="text" class="form-control" name="category_title[]" placeholder="Όνομα κατηγορίας...">
          <input type="hidden" name="category_id[]" value="">
          <button type="button" class="btn btn-sm btn-outline-danger remove-category">
              <i class="fa fa-times"></i>
          </button>
      `;
      document.getElementById("categoriesList").appendChild(row);
    });

  // ── Remove category row ──
  document
    .getElementById("categoriesList")
    ?.addEventListener("click", function (e) {
      if (e.target.closest(".remove-category")) {
        e.target.closest(".category-row").remove();
      }
    });

  // ── Kanban Drag & Drop ──
  let draggedCard = null;

  function initKanban() {
    const cards = document.querySelectorAll(".kanban-card");
    const columns = document.querySelectorAll(".kanban-cards");

    if (!cards.length) return;

    const wrapper = document.getElementById("kanban-board-wrapper");
    const isDraggable = wrapper && wrapper.dataset.draggable === "true";

    if (!isDraggable) return;

    cards.forEach((card) => {
      card.addEventListener("dragstart", onDragStart);
      card.addEventListener("dragend", onDragEnd);
    });

    columns.forEach((col) => {
      col.addEventListener("dragover", onDragOver);
      col.addEventListener("dragleave", onDragLeave);
      col.addEventListener("drop", onDrop);
    });
  }

  function onDragStart(e) {
    draggedCard = this;
    setTimeout(() => this.classList.add("dragging"), 0);
    e.dataTransfer.effectAllowed = "move";
  }

  function onDragEnd() {
    this.classList.remove("dragging");
    document
      .querySelectorAll(".kanban-cards")
      .forEach((c) => c.classList.remove("drag-over"));
    draggedCard = null;
    updateColumnCounts();
  }

  function onDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = "move";
    this.classList.add("drag-over");
    const empty = this.querySelector(".kanban-empty");
    if (empty) {
      empty.classList.add("d-none");
    }
  }

  function onDragLeave(e) {
    if (!this.contains(e.relatedTarget)) {
      this.classList.remove("drag-over");
      const empty = this.querySelector(".kanban-empty");
      const count = this.querySelectorAll(".kanban-card").length;
      if (empty && count === 0) {
        empty.classList.remove("d-none");
      }
    }
  }

  function onDrop(e) {
    e.preventDefault();
    this.classList.remove("drag-over");

    if (!draggedCard) return;
    if (draggedCard.closest(".kanban-cards") === this) return;

    this.appendChild(draggedCard);

    draggedCard.addEventListener("dragstart", onDragStart);
    draggedCard.addEventListener("dragend", onDragEnd);

    updateColumnCounts();

    saveCardCategory(draggedCard.dataset.postId, this.dataset.categoryId);
  }

  function updateColumnCounts() {
    document.querySelectorAll(".kanban-column").forEach((col) => {
      const cardsContainer = col.querySelector(".kanban-cards");
      const count = cardsContainer.querySelectorAll(".kanban-card").length;
      col.querySelector(".kanban-column-count").textContent = count;

      const empty = cardsContainer.querySelector(".kanban-empty");
      if (empty) {
        if (count === 0) {
          empty.classList.remove("d-none");
        } else {
          empty.classList.add("d-none");
        }
      }
    });
  }

  function saveCardCategory(postId, categoryId) {
    const ajaxUrl = window.stickyNotesAjaxUrl;
    if (!ajaxUrl) {
      console.error("stickyNotesAjaxUrl is not defined");
      return;
    }

    fetch(ajaxUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify({
        action: "move_post",
        post_id: parseInt(postId),
        category_id:
          categoryId === "0" || !categoryId ? null : parseInt(categoryId),
      }),
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.status === "ok") {
          showToast("Η σημείωση μεταφέρθηκε!");
        } else {
          showToast("Σφάλμα κατά τη μεταφορά", "error");
        }
      })
      .catch(() => showToast("Σφάλμα", "error"));
  }

  initKanban();

  function showToast(message, type = "success") {
    let toast = document.getElementById("stickyToast");

    if (!toast) {
      toast = document.createElement("div");
      toast.id = "stickyToast";
      toast.className = "sticky-toast";
      document.body.appendChild(toast);
    }

    toast.className = `sticky-toast ${type}`;
    toast.innerHTML = `<i class="fa ${
      type === "success" ? "fa-check-circle" : "fa-times-circle"
    }"></i> ${message}`;

    requestAnimationFrame(() => toast.classList.add("show"));

    clearTimeout(toast._hideTimeout);
    toast._hideTimeout = setTimeout(() => {
      toast.classList.remove("show");
    }, 2500);
  }

  let pendingDeleteId = null;
  const confirmDeleteModalEl = document.getElementById("confirmDeleteModal");
  const confirmDeleteModal = confirmDeleteModalEl
    ? new bootstrap.Modal(confirmDeleteModalEl)
    : null;

  document.addEventListener("click", function (e) {
    const btn = e.target.closest(".note-delete-btn");
    if (!btn) return;

    e.preventDefault();
    if (!confirmDeleteModal) return;
    pendingDeleteId = btn.dataset.postId;
    confirmDeleteModal.show();
  });

  document
    .getElementById("confirmDeleteBtn")
    ?.addEventListener("click", function () {
      if (!pendingDeleteId) return;

      confirmDeleteModal.hide();

      fetch(window.stickyNotesAjaxUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({
          action: "delete_post",
          post_id: parseInt(pendingDeleteId),
        }),
      })
        .then((r) => r.json())
        .then((data) => {
          if (data.status === "ok") {
            const card = document
              .querySelector(
                `.note-delete-btn[data-post-id="${pendingDeleteId}"]`
              )
              ?.closest(".sticky-note");
            if (card) {
              card.style.transition = "opacity 0.3s, transform 0.3s";
              card.style.opacity = "0";
              card.style.transform = "scale(0.9)";
              setTimeout(() => {
                card.remove();
                updateColumnCounts();
              }, 300);
            }
            showToast(
              window.stickyNotesLang?.deleted || "Η σημείωση διαγράφηκε"
            );
          } else {
            showToast(
              window.stickyNotesLang?.deleteError || "Σφάλμα κατά τη διαγραφή",
              "error"
            );
          }
          pendingDeleteId = null;
        })
        .catch(() => {
          showToast(
            window.stickyNotesLang?.connError || "Σφάλμα σύνδεσης",
            "error"
          );
          pendingDeleteId = null;
        });
    });

  const previewNote = document.getElementById("notePreview");
  const previewContent = document.getElementById("previewContent");
  const contentArea = document.getElementById("content");

  if (previewNote && contentArea) {
    contentArea.addEventListener("input", function () {
      previewContent.textContent =
        this.value.trim() ||
        window.stickyNotesLang?.previewPlaceholder ||
        "...";
    });

    document.querySelectorAll('input[name="color"]').forEach((radio) => {
      radio.addEventListener("change", function () {
        previewNote.style.backgroundColor = this.value;

        document
          .querySelectorAll(".color-swatch-label")
          .forEach((l) => l.classList.remove("selected"));
        this.closest(".color-swatch-label").classList.add("selected");
      });
    });

    const checkedColor = document.querySelector('input[name="color"]:checked');
    if (checkedColor) {
      previewNote.style.backgroundColor = checkedColor.value;
    }

    if (contentArea.value.trim()) {
      previewContent.textContent = contentArea.value.trim();
    }
  }

  // ── Category Reorder ──
  const catList = document.getElementById("categoriesList");
  let draggedRow = null;

  if (catList) {
    catList.addEventListener("dragstart", function (e) {
      const row = e.target.closest(".category-row");
      if (!row) return;
      draggedRow = row;
      setTimeout(() => row.classList.add("dragging"), 0);
    });

    catList.addEventListener("dragend", function (e) {
      const row = e.target.closest(".category-row");
      if (row) row.classList.remove("dragging");
      document
        .querySelectorAll(".category-row")
        .forEach((r) => r.classList.remove("drag-over-row"));
      draggedRow = null;
      updateSortOrders();
    });

    catList.addEventListener("dragover", function (e) {
      e.preventDefault();
      const row = e.target.closest(".category-row");
      if (!row || row === draggedRow) return;
      document
        .querySelectorAll(".category-row")
        .forEach((r) => r.classList.remove("drag-over-row"));
      row.classList.add("drag-over-row");

      const rect = row.getBoundingClientRect();
      const midpoint = rect.top + rect.height / 2;
      if (e.clientY < midpoint) {
        catList.insertBefore(draggedRow, row);
      } else {
        catList.insertBefore(draggedRow, row.nextSibling);
      }
    });
  }

  function updateSortOrders() {
    document.querySelectorAll(".category-row").forEach((row, index) => {
      const sortInput = row.querySelector('input[name="category_sort[]"]');
      if (sortInput) sortInput.value = index;
    });
  }
});
