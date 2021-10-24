export interface Image {
  id?: number;
  src: string;
  srcset?: string;
  src2x?: string;
  h?: number;
  w?: number;
  name?: string;
  size?: number;
  alt?: string;
  parent?: Image['id'];
  description?: string;
}
