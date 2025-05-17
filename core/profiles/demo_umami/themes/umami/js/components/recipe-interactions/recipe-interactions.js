((Drupal, once) => {
  Drupal.behaviors.recipeInteractions = {
    attach(context) {
      once('recipe-interactions', '.recipe-rating', context).forEach((element) => {
        element.querySelectorAll('input[type="radio"]').forEach((input) => {
          input.addEventListener('change', (e) => {
            const rating = e.target.value;
            const url = e.target.dataset.url;
            fetch(url, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: 'rating=' + rating,
            })
              .then((res) => res.json())
              .then((data) => {
                if (data.average) {
                  element.querySelector('.average-rating').textContent = data.average;
                }
              });
          });
        });
      });
      once('recipe-favorite', '.recipe-favorite', context).forEach((button) => {
        button.addEventListener('click', (e) => {
          e.preventDefault();
          fetch(button.dataset.url, { method: 'POST' })
            .then((res) => res.json())
            .then((data) => {
              button.classList.toggle('is-favorited', data.favorited);
            });
        });
      });
    },
  };
})(Drupal, once);
