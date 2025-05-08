<?php
// views/home.php
?>
<article class="home-content">
    <section>
	    <h2>Naplónak indult.</h2>
        <p>Bemutató bloggá vált.</p>
        <h1>Aztán átalakult valami mássá.</h1>
        <p>Ablakká, amelyben kitekintek a világra, a világ meg betekinthet a gondolataimba: késekről, every day carry felszerelésekről, és az ezek mögött meghúzódó filozófiáról.</p>
        <h1>Aztán ennél is több lett.</h1>
        <p> Egy közösség, amelyben együtt, hasonló értékek mentén dolgozunk azért, hogy egy minőségi, kissé talán régimódi találkahely legyen ez az online térben.</p>
    </section>
</article>

    <!-- VIDEÓ SLIDER SZEKCIÓ -->
<section class="media-section">
        <div class="video-slider">
            <button class="slider-nav prev" aria-label="Előző videó">&larr;</button>
            <div class="slides">
				<div class="slide">
					<h3>Saját videó</h3>
					<div class="video-container">
					<video src="assets/videos/sajat.mp4"
						controls preload="metadata">
						Your browser does not support the video tag.
					</video>
					</div>
				</div>
                <div class="slide">
                    <h3>YouTube videó</h3>
                    <div class="video-container">
                        <iframe width="560" height="315"
                                src="https://www.youtube.com/embed/fARhNFnuVPU?si=EH0uRHJiZf_20mJN"
                                title="YouTube video player" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                        </iframe>
                    </div>
                </div>
            </div>
            <button class="slider-nav next" aria-label="Következő videó">&rarr;</button>
        </div>
    </section>
</article>

<script>
// Egyszerű slider JS
document.addEventListener('DOMContentLoaded', () => {
  const slides = document.querySelector('.video-slider .slides');
  if (!slides) return;
  const total = slides.children.length;
  let idx = 0;
  function update() {
    slides.style.transform = `translateX(-${idx * 100}%)`;
  }
  document.querySelector('.slider-nav.prev').addEventListener('click', () => {
    idx = (idx - 1 + total) % total;
    update();
  });
  document.querySelector('.slider-nav.next').addEventListener('click', () => {
    idx = (idx + 1) % total;
    update();
  });
});
</script>

  <!-- Beágyazott térkép szekció -->
  <section class="map-section">
    <h2>Hol találsz minket?</h2>
    <div class="map-container">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2726.337716390951!2d19.666564677574648!3d46.896076271133516!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4743da7a6c479e1d%3A0xc8292b3f6dc69e7f!2sNeumann%20J%C3%A1nos%20Egyetem%20GAMF%20M%C5%B1szaki%20%C3%A9s%20Informatikai%20Kar!5e0!3m2!1shu!2shu!4v1746283416852!5m2!1shu!2shu"
		allowfullscreen
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade">
      </iframe>
    </div>
  </section>