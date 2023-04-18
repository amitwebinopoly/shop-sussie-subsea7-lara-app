import { useState, useCallback } from "react";
import {
  Card,
  Heading,
  TextContainer,
  DisplayText,
  TextStyle,
  DropZone, Stack, Caption
} from "@shopify/polaris";
import { Toast } from "@shopify/app-bridge-react";
import { useAppQuery, useAuthenticatedFetch } from "../hooks";

export function UploadLogo() {
  const emptyToastProps = { content: null };
  const [isLoading, setIsLoading] = useState(false);
  const [toastProps, setToastProps] = useState(emptyToastProps);
  const fetch = useAuthenticatedFetch();

  const [logo_file, set_logo_file] = useState([]);

  const toastMarkup = toastProps.content && (
      <Toast {...toastProps} onDismiss={() => setToastProps(emptyToastProps)} />
  );

    const { data: dataGetLogo, refetch: refetchGetLogo, isLoading: isLoadingGetLogo, isRefetching: isRefetchingGetLogo } = useAppQuery({
        url: "/api/get_upload_logo",
        reactQueryOptions: {
            onSuccess: () => {

            },
        },
    });
  
  const handleUploadLogo = async () => {

    if(logo_file.length==0){
        setToastProps({
            content: "Please choose logo",
            error: true,
        });
        return;
    }

    setIsLoading(true);
    var formData = new FormData();
    //formData.append('shop', 'xyz@myshopify.com');

    formData.append("logo_file", logo_file[0]);

    const rawResponse = await fetch('/api/upload_logo', {
      method: 'POST',
      body: formData
    });
    const obj = await rawResponse.json();
    if(obj.success == 'true'){
        setIsLoading(false);
        set_logo_file([]);
        setToastProps({
            content: "Logo uploaded successfully."
        });
        await refetchGetLogo();
    }else{
        setIsLoading(false);
        setToastProps({
            content: obj.message,
            error: true
        });
    }

};

  //Dropzone
  const handleDropZoneDrop = useCallback(
    (_dropFiles, acceptedFiles, _rejectedFiles) =>
      //set_logo_file((files) => [...files, ...acceptedFiles]),
        set_logo_file(acceptedFiles),
      []
  );
  const fileUpload = !logo_file.length && (
      <DropZone.FileUpload />
  );
  const uploadedFiles = logo_file.length > 0 && (
      <Stack vertical>
        {logo_file.map((file, index) => (
          <Stack alignment="center" key={index}>
            <div> {file.name} <Caption>{file.size} bytes</Caption> </div>
          </Stack>
        ))}
      </Stack>
  );

  return (
    <>
      {toastMarkup}
      <Card
        title="Upload Logo"
        sectioned
        primaryFooterAction={{
          content: "Upload",
          onAction: handleUploadLogo,
          loading: isLoading,
        }}
      >
        <TextContainer spacing="loose">
            {(dataGetLogo && dataGetLogo.success == 'true' && dataGetLogo.data.ss_logo!="")?(
                <img src={dataGetLogo.data.ss_logo} style={{width:"200px"}} />
            ):null}
          <p>Select Logo for Checkout Page</p>
          <DropZone
            onDrop={handleDropZoneDrop}
            variableHeight
            allowMultiple={false}
          >
            {uploadedFiles}
            {fileUpload}
          </DropZone>

        </TextContainer>
      </Card>
    </>
  );
}
