<section class="creative-testimonial--slider">
  <div class="testimonial-inner" style="background-image: url(https://i.ibb.co/99Kc53w7/demo-corporate-bg-02.png);">
    <div class="testimonial-row">
      <h2 class="testimonial-heading">Thousands Trust Waqt Finance for Fast & Easy Loans</h2>
      <div class="testimonial-wrap">
        <div class="swiper-container">
          <div class="swiper-wrapper">
            <!-- Slide 1 -->
            <div class="swiper-slide">
              <div class="swiper-slide--inner">
                <div class="slide-avatar">
                  <img src="./assets/images/kalam.webp" style="border-radius: 50%;" alt="Kalam">
                </div>
                <div class="testimonial-detail">
                  <h4 class="w-h">Waqt Finance</h4>
                  <p>I needed a personal loan urgently and the process was very smooth. The approval was quick and the amount was credited within hours. Highly recommended for anyone looking for fast financial support.</p>
                  <span>Kalam Khan, Personal Loan Customer</span>
                </div>
              </div>
            </div>
            <!-- Slide 2 -->
            <div class="swiper-slide">
              <div class="swiper-slide--inner">
                <div class="slide-avatar">
                  <img src="./assets/images/sumit.webp"  alt="Sumit">
                </div>
                <div class="testimonial-detail">
                   <h4 class="w-h">Waqt Finance</h4>
                  <p>This platform helped me get a business loan without complicated paperwork. Their team guided me at every step and the loan was approved quickly. Great service for small business owners.</p>
            <span>Sumit Sharma, Business Owner</span>
                </div>
              </div>
            </div>
            <!-- Slide 3 -->
            <div class="swiper-slide">
              <div class="swiper-slide--inner">
                <div class="slide-avatar">
                  <img src="./assets/images/sadik.webp"  alt="Sadik Ali">
                </div>
                <div class="testimonial-detail">
                  <h4 class="w-h">Waqt Finance</h4>
                  <p>I was impressed by how simple the loan application process was. Everything was online and transparent. I received the funds very quickly when I needed them the most.</p>
                  <span>Sadik Ali, Salaried Professional</span>
                </div>
              </div>
            </div>
          </div>
          <!-- Navigation -->
          <div class="swiper-button-next slide-btns"></div>
          <div class="swiper-button-prev slide-btns"></div>
        </div>
      </div>

     

    </div>
  </div>
</section>



<script>
  // Initialize Swiper
  document.addEventListener("DOMContentLoaded", function() {
    const swiper = new Swiper('.swiper-container', {
      loop: true,
      autoplay: {
        delay: 4000,
        disableOnInteraction: false,
      },
      keyboard: {
        enabled: true,
        onlyInViewport: true,
      },
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev"
      },
      effect: 'slide',
    });
  });
</script>