(() => {
    try {
        const savedTheme = localStorage.getItem('sam-software-theme');
        const savedSeason = localStorage.getItem('sam-software-season');
        const seasons = ['spring', 'summer', 'autumn', 'winter'];

        document.body.classList.toggle('dark', savedTheme === 'dark');

        if (seasons.includes(savedSeason)) {
            document.body.dataset.season = savedSeason;
        }
    } catch (error) {
    }
})();
