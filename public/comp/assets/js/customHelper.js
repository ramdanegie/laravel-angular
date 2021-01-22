 function countAge(birthday, dataNow) {
        // debugger;
        if (birthday === undefined || birthday === '')
          birthday = Date.now();
        else {
          if (birthday instanceof Date) {

          } else {
            var arr = birthday.split('-');
            if (arr[0].length === 4) {
              birthday = new Date(arr[0], arr[1], arr[2]);
            } else {
              birthday = new Date(arr[2], arr[1], arr[0]);
            }
          }

        }
        if (dataNow === undefined)
          dataNow = Date.now();
        var ageDifMs = dataNow - birthday;
        var ageDate = new Date(ageDifMs); // miliseconds from epoch
        var year = ageDate.getFullYear() - 1970;
        if (year <= -1)
          year = 0;
        var day = ageDate.getDate() - 1;
        var date = new Date(year, ageDate.getMonth(), day);
        return {
          year: year,
          month: ageDate.getMonth(),
          day: day,
          date: date
        };
      };