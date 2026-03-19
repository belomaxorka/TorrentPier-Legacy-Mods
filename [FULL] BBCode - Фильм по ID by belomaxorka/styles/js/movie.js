/**
 * Movie Widget Library
 */

class MovieWidget {
  constructor(options = {}) {
    this.options = {
      collapseDelay: options.collapseDelay || 300,
      autoInit: options.autoInit !== false
    };

    this.requestQueue = [];
    this.isProcessing = false;

    if (this.options.autoInit) {
      this.init();
    }
  }

  /**
   * Инициализация библиотеки
   */
  init() {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.scanAndLoad());
    } else {
      this.scanAndLoad();
    }
  }

  /**
   * Сканирование DOM и загрузка данных
   */
  scanAndLoad() {
    const elements = document.querySelectorAll('[data-kinopoisk], [data-imdb]');
    
    elements.forEach(element => {
      const kinopoiskId = element.getAttribute('data-kinopoisk');
      const imdbId = element.getAttribute('data-imdb');

      this.loadMovieData(element, { kinopoiskId, imdbId });
    });
  }

  /**
   * Загрузка данных о фильме с использованием collapse балансера
   */
  loadMovieData(element, ids) {
    this.requestQueue.push({ element, ids });
    this.processQueue();
  }

  /**
   * Обработка очереди запросов с collapse балансером
   */
  async processQueue() {
    if (this.isProcessing || this.requestQueue.length === 0) {
      return;
    }

    this.isProcessing = true;

    while (this.requestQueue.length > 0) {
      const { element, ids } = this.requestQueue.shift();
      
      try {
        this.renderPlayer(element, ids);
      } catch (error) {
        this.renderError(element, error);
      }

      // Collapse delay между загрузками
      if (this.requestQueue.length > 0) {
        await this.delay(this.options.collapseDelay);
      }
    }

    this.isProcessing = false;
  }

  /**
   * Построение URL для Collaps балансера
   */
  buildCollapseUrl(ids) {
    // Collaps поддерживает только Kinopoisk и IMDB
    if (ids.kinopoiskId) {
      return `https://api.delivembd.ws/embed/kp/${ids.kinopoiskId}`;
    } else if (ids.imdbId) {
      return `https://api.delivembd.ws/embed/imdb/${ids.imdbId}`;
    }
    
    throw new Error('Не указан ID фильма (Kinopoisk или IMDB)');
  }

  /**
   * Отрисовка плеера
   */
  renderPlayer(element, ids) {
    const iframeUrl = this.buildCollapseUrl(ids);

    // Если у элемента не задана высота, устанавливаем соотношение 16:9
    if (!element.style.height || element.style.height === 'auto') {
      const width = element.offsetWidth || 720;
      const height = Math.round(width * 9 / 16);
      element.style.height = height + 'px';
    }

    // Создаем iframe напрямую без wrapper
    const iframe = document.createElement('iframe');
    iframe.src = iframeUrl;
    iframe.style.cssText = `
      width: 100%;
      height: 100%;
      border: none;
      background: #000;
      display: block;
    `;
    iframe.allowFullscreen = true;
    iframe.allow = 'autoplay; encrypted-media; fullscreen; picture-in-picture';

    // Очищаем элемент и добавляем плеер
    element.innerHTML = '';
    element.appendChild(iframe);
  }

  /**
   * Отрисовка ошибки
   */
  renderError(element, error) {
    element.innerHTML = `
      <div style="
        padding: 25px;
        background: #fee;
        border: 2px solid #fcc;
        border-radius: 12px;
        color: #c33;
        text-align: center;
      ">
        <p style="margin: 0; font-weight: 600;">Ошибка загрузки плеера: ${error.message}</p>
      </div>
    `;
  }

  /**
   * Задержка для collapse балансера
   */
  delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
}

// Экспорт для использования
if (typeof module !== 'undefined' && module.exports) {
  module.exports = MovieWidget;
}

// Глобальная инициализация
if (typeof window !== 'undefined') {
  window.MovieWidget = MovieWidget;
  
  // Автоматическая инициализация при загрузке
  new MovieWidget();
}
