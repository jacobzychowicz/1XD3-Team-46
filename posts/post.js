window.addEventListener('load', function () {
    addPostForm = document.getElementById('addPost');

    addPostForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const postName = document.getElementById('postName').value;
        const postDesc = document.getElementById('postDesc').value;
        const postAttach = document.getElementById('postAttach').value;

        const postData = {
            title: postName, 
            description: postDesc,
            attachment: postAttach,
            user_id: 1
        };

        try {
            const response = await fetch('add_post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(postData)
            });

            if (response.ok) {
                const result = await response.json();
                alert('Post Created Successfully');
            } else {
                console.error('Server Error:', response.statusText);
            }
        } catch (error) {
            console.error('Network Error:', error);
        }
    });
});