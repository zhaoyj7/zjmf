const theme = document.documentElement.getAttribute("theme") || "default";
const initTheme = () => {
  // 设置根元素的主题
  document.documentElement.setAttribute("theme", theme);
  // 加载对应的主题
  const link = document.createElement("link");
  link.rel = "stylesheet";
  link.href = `${url}theme/${theme}/index.css`;
  document.head.appendChild(link);
};
initTheme();
