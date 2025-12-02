import './bootstrap';
import Alpine from '@alpinejs/csp';

window.Alpine = Alpine;

Alpine.data('navigation', () => ({
    navOpen: false,
    toggle() {
        this.navOpen = !this.navOpen;
    }
}));

Alpine.data('dropdown', () => ({
    open: false,
    toggle() {
        this.open = !this.open;
    },
    close() {
        this.open = false;
    }
}));

Alpine.data('modal', () => ({
    show: false,
    focusables: [],
    init() {
        this.focusables = [...this.$el.querySelectorAll('a, button, input, textarea, select, [tabindex]:not([tabindex="-1"])')];
    },
    openModal() {
        this.show = true;
    },
    closeModal() {
        this.show = false;
    }
}));

Alpine.data('flashMessage', () => ({
    show: true,
    init() {
        setTimeout(() => this.show = false, 2000);
    }
}));

Alpine.data('addressForm', (initialEditing = false) => ({
    editing: initialEditing
}));

Alpine.data('passwordToggle', () => ({
    show: false,
    toggle() {
        this.show = !this.show;
    }
}));

Alpine.data('modalWindow', (name, initialShow = false) => ({
    show: initialShow,
    name: name,
    init() {
        this.$watch('show', value => {
            if (value) {
                document.body.classList.add('overflow-y-hidden');
            } else {
                document.body.classList.remove('overflow-y-hidden');
            }
        });
    },
    handleOpen(event) {
        if (event.detail === this.name) this.show = true;
    },
    handleClose(event) {
        if (event.detail === this.name) this.show = false;
    },
    close() {
        this.show = false;
    }
}));

Alpine.data('eventDispatcher', (eventName, detail = null) => ({
    trigger() {
        this.$dispatch(eventName, detail);
    }
}));

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        Alpine.start();
        initPhoneInputs();
    });
} else {
    Alpine.start();
    initPhoneInputs();
}

function initPhoneInputs() {
    document.querySelectorAll('.phone-input').forEach(input => {
        input.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').substring(0, 13);
        });
    });
}


