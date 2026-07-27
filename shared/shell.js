document.addEventListener('DOMContentLoaded', () => {
	const sidebar = document.querySelector('.sidebar');
	const toggle = document.querySelector('.sidebar-toggle');
	const scrim = document.querySelector('.sidebar-scrim');

	const closeSidebar = () => {
		sidebar?.classList.remove('open');
		scrim?.classList.remove('open');
	};

	toggle?.addEventListener('click', () => {
		sidebar?.classList.toggle('open');
		scrim?.classList.toggle('open');
	});

	scrim?.addEventListener('click', closeSidebar);

	document.querySelectorAll('[data-modal-open]').forEach((btn) => {
		btn.addEventListener('click', () => {
			const modal = document.getElementById(btn.getAttribute('data-modal-open'));
			modal?.classList.add('open');
		});
	});

	document.querySelectorAll('[data-modal-close]').forEach((btn) => {
		btn.addEventListener('click', () => {
			btn.closest('.modal-backdrop')?.classList.remove('open');
		});
	});

	document.querySelectorAll('.modal-backdrop').forEach((backdrop) => {
		backdrop.addEventListener('click', (e) => {
			if (e.target === backdrop) backdrop.classList.remove('open');
		});
	});

	const profileTrigger = document.querySelector('.topbar-profile');
	const profileMenu = document.querySelector('.profile-menu');

	if (profileTrigger && profileMenu) {
		profileTrigger.addEventListener('click', (e) => {
			e.stopPropagation();
			profileMenu.classList.toggle('open');
		});
		document.addEventListener('click', () => profileMenu.classList.remove('open'));
	}
});
