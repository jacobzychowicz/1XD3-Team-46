window.addEventListener('load', function () {
    const teamMembers = ["Edgar Dizon", "Noah Mills", "Jacob Zychowicz", "Jamie Hammill"];
    let index = 0;
    const nameElement = document.querySelector('.names');

    function cycleName() {
        nameElement.classList.add('exit');
        nameElement.classList.remove('visible');

        setTimeout(() => {
            nameElement.classList.add('no-transition');
            nameElement.classList.remove('exit'); 
            
            nameElement.textContent = teamMembers[index];
            index = (index + 1) % teamMembers.length;

            void nameElement.offsetWidth;

            nameElement.classList.remove('no-transition');
            nameElement.classList.add('visible');
        }, 600); 
    }

    nameElement.textContent = teamMembers[0];
    nameElement.classList.add('visible');
    index = 1;

    setInterval(cycleName, 3000);
});