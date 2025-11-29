document.addEventListener('DOMContentLoaded', function() {
    const containers = document.querySelectorAll('.stc-testimonials-carousel');

    containers.forEach(container => {
        const items = container.querySelectorAll('.stc-testimonial-item');
        if (items.length === 0) return;

        let currentIndex = 0;
        const totalItems = items.length;
        const intervalTime = 6000; // 6 seconds

        // Create navigation arrows
        const prevBtn = document.createElement('button');
        prevBtn.className = 'stc-nav-btn stc-prev';
        prevBtn.innerHTML = '&lsaquo;';
        
        const nextBtn = document.createElement('button');
        nextBtn.className = 'stc-nav-btn stc-next';
        nextBtn.innerHTML = '&rsaquo;';

        container.appendChild(prevBtn);
        container.appendChild(nextBtn);

        // Function to show slide
        function showSlide(index) {
            items.forEach(item => item.style.display = 'none');
            items[index].style.display = 'block';
            currentIndex = index;
        }

        // Initial display
        showSlide(0);

        // Event listeners
        prevBtn.addEventListener('click', () => {
            let newIndex = currentIndex - 1;
            if (newIndex < 0) newIndex = totalItems - 1;
            showSlide(newIndex);
            resetTimer();
        });

        nextBtn.addEventListener('click', () => {
            let newIndex = currentIndex + 1;
            if (newIndex >= totalItems) newIndex = 0;
            showSlide(newIndex);
            resetTimer();
        });

        // Auto advance
        let timer = setInterval(() => {
            let newIndex = currentIndex + 1;
            if (newIndex >= totalItems) newIndex = 0;
            showSlide(newIndex);
        }, intervalTime);

        function resetTimer() {
            clearInterval(timer);
            timer = setInterval(() => {
                let newIndex = currentIndex + 1;
                if (newIndex >= totalItems) newIndex = 0;
                showSlide(newIndex);
            }, intervalTime);
        }
    });
});
