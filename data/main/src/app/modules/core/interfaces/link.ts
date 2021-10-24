export interface Link {
  text: string;
  path: any[] | string;
  redirect?: any[] | string;

  code?: string;
  fragment?: string;
  queryParams?: { [k: string]: any };
  children?: Link[];

  /** Если true, то не показывать ссылку */
  phantom?: boolean;

  /** Счетчик для доп. информации о ссылке */
  count?: number;

  /** Если true, используем href */
  external?: boolean;
}
