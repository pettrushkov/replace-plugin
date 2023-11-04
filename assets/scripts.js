(function () {
    document.addEventListener("DOMContentLoaded", () => {

        searchFormSubmit();
        replaceFormsSubmit();

    });
})();

const replaceFormsSubmit = () => {
    const forms = document.querySelectorAll('.word-changer-change-form');

    forms.forEach(form => {

        form.addEventListener('submit', (e) => {
            e.preventDefault();

            const oldVal = document.querySelector('.word-changer-search input[name="keyword"]').value;

            const data = new FormData(form);
            data.append('action', 'replace_form');
            data.append('old_val', oldVal);
            data.append('change_field', form.getAttribute('data-change-field'));

            fetch(front_vars.ajax_url, {
                method: "POST",
                credentials: 'same-origin',
                body: data
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data) {
                        const answer = document.querySelector('.word-changer-answer');

                        answer.style.display = 'block';
                        answer.innerHTML = '<p class="success">Success</p>';
                    }
                })
                .catch((error) => {
                    console.log('Replace Plugin error');
                    console.warn(error);
                });
        });

    });
}

const searchFormSubmit = () => {
    const form = document.querySelector('.word-changer-search'),
        answer = document.querySelector('.word-changer-answer');

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const data = new FormData(form);
        data.append('action', 'search_form');

        fetch(front_vars.ajax_url, {
            method: "POST",
            credentials: 'same-origin',
            body: data
        })
            .then((response) => response.json())
            .then((data) => {
                if (data) {
                    clearResults();

                    // clear answer block
                    answer.style.display = 'none';
                    answer.innerHTML = '';

                    // append results to cols
                    appendItemsToResults(data, 'title', 'post_titles', 'post_title');
                    appendItemsToResults(data, 'content', 'post_contents', 'post_content');
                    appendItemsToResults(data, 'meta-title', 'post_metatitle', 'meta_value');
                    appendItemsToResults(data, 'meta-description', 'post_metadesc', 'meta_value');
                }
            })
            .catch((error) => {
                console.log('Replace Plugin error');
                console.warn(error);
            });
    });
}

// Clear results list
const clearResults = () => {
    const results = document.querySelectorAll('.word-changer-col-results');

    results.forEach(result => {
        result.innerHTML = '';
    });
}

function appendItemsToResults(data, colClassName, dataArray, postValue) {
    const results = document.querySelector(`.word-changer-col--${colClassName} .word-changer-col-results`);

    if (data[dataArray]) {
        data[dataArray].forEach(post => {
            const postID = post.ID ?? post.post_id;
            results.innerHTML += `<li>post ID: ${postID}<br />${post[postValue]}</li>`;
        });
    }
}