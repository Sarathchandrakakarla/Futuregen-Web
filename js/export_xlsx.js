let ExcelJS = null;

// ======================================================
// LOAD EXCELJS
// ======================================================

(async () => {
  try {
    ExcelJS = (await import("https://cdn.jsdelivr.net/npm/exceljs/+esm"))
      .default;

  } catch (err) {
    console.error("ExcelJS Load Failed", err);
  }
})();

// ======================================================
// GLOBAL EXPORT FUNCTION
// ======================================================

window.exportTableToExcel = async function ({
  tableId,
  filename = "Report",
  hiddenColumns = [],
}) {
  try {
    // ======================================================
    // CHECK LIBRARY
    // ======================================================

    if (!ExcelJS) {
      alert("ExcelJS still loading. Please try again.");

      return;
    }

    // ======================================================
    // GET TABLE
    // ======================================================

    const originalTable = document.getElementById(tableId);

    if (!originalTable) {
      alert("Table not found");

      return;
    }

    // ======================================================
    // CLONE TABLE
    // IMPORTANT:
    // getComputedStyle() needs DOM attachment
    // ======================================================

    const clonedTable = originalTable.cloneNode(true);

    clonedTable.style.position = "absolute";
    clonedTable.style.left = "-99999px";
    clonedTable.style.top = "-99999px";

    document.body.appendChild(clonedTable);

    // ======================================================
    // REMOVE HIDDEN COLUMNS
    // ======================================================

    const headers = clonedTable.querySelectorAll("thead th");
    const removeIndexes = [];

    headers.forEach((th, index) => {
      const heading = th.innerText.trim();

      if (
        th.classList.contains("excel-hide") ||
        hiddenColumns.some((colName) => heading === colName.trim())
      ) {
        removeIndexes.push(index);
      }
    });

    removeIndexes.reverse().forEach((colIndex) => {
      clonedTable.querySelectorAll("tr").forEach((row) => {
        if (row.children[colIndex]) {
          row.children[colIndex].remove();
        }
      });
    });

    // ======================================================
    // CREATE WORKBOOK
    // ======================================================

    const workbook = new ExcelJS.Workbook();

    const worksheet = workbook.addWorksheet("Sheet1");

    // ======================================================
    // TRACK OCCUPIED CELLS
    // ======================================================

    const occupied = {};

    // ======================================================
    // PROCESS TABLE ROWS
    // ======================================================

    const rows = clonedTable.querySelectorAll("tr");

    rows.forEach((row, rowIndex) => {
      const excelRowNumber = rowIndex + 1;

      let currentCol = 1;

      const cells = row.querySelectorAll("th, td");

      cells.forEach((cell) => {
        // ======================================================
        // SKIP MERGED OCCUPIED CELLS
        // ======================================================

        while (occupied[`${excelRowNumber}-${currentCol}`]) {
          currentCol++;
        }

        const excelCell = worksheet.getCell(excelRowNumber, currentCol);

        // ======================================================
        // CELL VALUE
        // ======================================================

        excelCell.value = cell.innerHTML
          .replace(/<br\s*\/?>/gi, "\n")
          .replace(/<[^>]*>/g, "")
          .trim();

        // ======================================================
        // COMPUTED STYLE
        // ======================================================

        const style = window.getComputedStyle(cell);

        // ======================================================
        // FONT
        // ======================================================

        const cleanFont = style.fontFamily
          .split(",")[0]
          .replace(/['"]/g, "")
          .trim();

        const bgARGB = getARGB(style.backgroundColor);

        let fontColor = getARGB(style.color) || "FF000000";

        // ======================================================
        // FORCE DARK TEXT FOR LIGHT/BOOTSTRAP TEXT CLASSES
        // ======================================================

        const rgbColor = style.color.replace(/\s/g, "");

        if (
          rgbColor === "rgb(255,255,255)" ||
          rgbColor === "rgb(248,249,250)" ||
          rgbColor === "rgb(250,250,250)" ||
          rgbColor === "rgba(255,255,255,1)"
        ) {
          fontColor = "FF000000";
        }

        excelCell.font = {
          name: cleanFont || "Calibri",

          size: Math.round((parseFloat(style.fontSize) * 72) / 96) || 11,

          bold:
            style.fontWeight === "bold" || parseInt(style.fontWeight) >= 600,

          italic: style.fontStyle === "italic",

          color: {
            argb: fontColor,
          },
        };

        // ======================================================
        // ALIGNMENT
        // ======================================================

        let horizontal = style.textAlign;

        if (horizontal === "start") {
          horizontal = "left";
        }

        if (horizontal === "end") {
          horizontal = "right";
        }

        excelCell.alignment = {
          horizontal: horizontal || "left",

          vertical: "middle",

          wrapText: true,
        };

        // ======================================================
        // BACKGROUND COLOR
        // ======================================================

        if (bgARGB) {
          excelCell.fill = {
            type: "pattern",

            pattern: "solid",

            fgColor: {
              argb: bgARGB,
            },
          };
        }

        // ======================================================
        // BORDER
        // ======================================================

        excelCell.border = {
          top: {
            style: "thin",
            color: { argb: "FF000000" },
          },

          bottom: {
            style: "thin",
            color: { argb: "FF000000" },
          },

          left: {
            style: "thin",
            color: { argb: "FF000000" },
          },

          right: {
            style: "thin",
            color: { argb: "FF000000" },
          },
        };

        // ======================================================
        // ROW HEIGHT
        // ======================================================

        const rowHeight = parseFloat(style.height);

        if (!isNaN(rowHeight)) {
          worksheet.getRow(excelRowNumber).height = Math.max(
            rowHeight * 0.75,
            20,
          );
        }

        // ======================================================
        // COLSPAN / ROWSPAN
        // ======================================================

        const colspan = parseInt(cell.colSpan || 1);

        const rowspan = parseInt(cell.rowSpan || 1);

        // ======================================================
        // MERGE CELLS
        // ======================================================

        if (colspan > 1 || rowspan > 1) {
          worksheet.mergeCells(
            excelRowNumber,
            currentCol,
            excelRowNumber + rowspan - 1,
            currentCol + colspan - 1,
          );
        }

        // ======================================================
        // MARK OCCUPIED CELLS
        // ======================================================

        for (let r = excelRowNumber; r < excelRowNumber + rowspan; r++) {
          for (let c = currentCol; c < currentCol + colspan; c++) {
            occupied[`${r}-${c}`] = true;
          }
        }

        currentCol += colspan;
      });
    });

    // ======================================================
    // REAPPLY BORDERS TO ALL CELLS
    // IMPORTANT:
    // ExcelJS sometimes drops borders
    // after merges
    // ======================================================

    worksheet.eachRow(
      {
        includeEmpty: true,
      },
      (row) => {
        row.eachCell(
          {
            includeEmpty: true,
          },
          (cell) => {
            if (!cell.border) {
              cell.border = {};
            }

            cell.border = {
              top: cell.border.top || {
                style: "thin",
                color: {
                  argb: "FF000000",
                },
              },

              bottom: cell.border.bottom || {
                style: "thin",
                color: {
                  argb: "FF000000",
                },
              },

              left: cell.border.left || {
                style: "thin",
                color: {
                  argb: "FF000000",
                },
              },

              right: cell.border.right || {
                style: "thin",
                color: {
                  argb: "FF000000",
                },
              },
            };
          },
        );
      },
    );

    // ======================================================
    // AUTO COLUMN WIDTH
    // ======================================================

    worksheet.columns.forEach((column) => {
      if (!column) return;

      let maxLength = 10;

      column.eachCell(
        {
          includeEmpty: true,
        },
        (cell) => {
          const value = cell.value ? cell.value.toString() : "";

          maxLength = Math.max(maxLength, value.length + 2);
        },
      );

      column.width = Math.min(maxLength, 50);
    });

    // ======================================================
    // EXPORT FILE
    // ======================================================

    const buffer = await workbook.xlsx.writeBuffer();

    const blob = new Blob([buffer], {
      type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });

    const link = document.createElement("a");

    link.href = URL.createObjectURL(blob);

    link.download = `${filename}.xlsx`;

    document.body.appendChild(link);

    link.click();

    document.body.removeChild(link);

    URL.revokeObjectURL(link.href);

    // ======================================================
    // REMOVE TEMP TABLE
    // ======================================================

    document.body.removeChild(clonedTable);
  } catch (err) {
    console.error("Excel Export Error:", err);

    alert("Excel Export Failed");
  }
};

// ======================================================
// RGB/RGBA -> ARGB
// ======================================================

function getARGB(color) {
  if (!color || color === "transparent" || color === "rgba(0, 0, 0, 0)") {
    return null;
  }

  const rgb = color.match(/\d+/g);

  if (!rgb) {
    return null;
  }

  const hex = rgb
    .slice(0, 3)
    .map((x) => parseInt(x).toString(16).padStart(2, "0"))
    .join("")
    .toUpperCase();

  return "FF" + hex;
}
