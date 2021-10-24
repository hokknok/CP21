export interface BackendQuery {
  action?: string;

  /**
   * Поле указывает, можно ли складывать результаты запроса
   * в localStorage и использовать как кеш.
   * По умолчанию true
   */
  canCache?: boolean;

  /**
   * Поле указывает, можно ли использовать
   * для кеша localStorage.
   * По умолчанию true
   */
  canUseLocalStorage?: boolean;

  /**
   * Любые данные для передачи в бекенд
   */
  data?: any;

  /**
   * Любые дополнительные поля
   */
  [key: string]: any;
}
